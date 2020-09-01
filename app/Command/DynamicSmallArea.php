<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Base\AbstractCommand;
use App\Services\Income\DynamicBigIncomeConfigService;
use App\Services\Income\DynamicIncomeService;
use App\Services\Income\DynamicSmallIncomeConfigService;
use App\Services\Income\DynamicSmallIncomeService;
use App\Services\Income\IncomeStatisticsService;
use App\Services\Mine\MinePoolService;
use App\Services\Mine\MineService;
use App\Services\Queue\QueueService;
use App\Services\Separate\SeparateWarehouseService;
use App\Services\Income\StaticIncomeService;
use App\Services\User\UserAssetsService;
use App\Services\User\UserRelationService;
use App\Services\User\UserWarehouseService;
use Carbon\Carbon;
use function GuzzleHttp\Psr7\str;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\Logger;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * @Command
 */
class DynamicSmallArea extends AbstractCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $signature = 'cmd:dynamic_small_area';

    /**
     * @var string
     */
    protected $day = null;

    /**
     * @Inject
     * @var UserRelationService
     */
    protected $urs;

    /**
     * @Inject
     * @var MinePoolService
     */
    protected $mps;

    /**
     * @Inject
     * @var DynamicSmallIncomeConfigService
     */
    protected $dscs;

    /**
     * @Inject
     * @var UserAssetsService
     */
    protected $uas;

    /**
     * @Inject
     * @var DynamicSmallIncomeService
     */
    protected $dsis;

    /**
     * @Inject
     * @var IncomeStatisticsService
     */
    protected $iss;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct();
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('动态大区收益计算脚本');
    }

    public function handle()
    {
        $this->day = Carbon::now()->format('Y-m-d');
        $pools = $this->mps->mineList(['status' => 1]); //查询启用的矿池
        foreach ($pools as $pool) {
            $dynamic_small_config = $this->dscs->getConfig([
                'coin_symbol' => $pool->coin_symbol
            ])->first();
            //至少要两个一级分销商才有动态小区收益
            $this->urs->findUserList([
                'child_user_ids' => [
                    'condition' => 'function',
                    'data' => function ($query) {
                        $query->whereRaw('json_length(child_user_ids) > 1');
                    }
                ],
                'chunk' => function (Collection $user_relation) use ($dynamic_small_config) {
                    $this->chunk($user_relation, $dynamic_small_config);
                }
            ]);
            try {
                $sum_income = $this->dsis->sumIncome([
                    'today_small_income' => 'small_income'
                ], [
                    'day' => $this->day,
                    'coin_symbol' => $pool->coin_symbol
                ]);
                $this->iss->createStatistics([
                    'day' => $this->day,
                    'coin_symbol' => $pool->coin_symbol,
                    'small_dynamic_num' => $sum_income->today_small_income ?: '0'
                ]);
            } catch (\Throwable $e) {
                $this->output->writeln($e->getMessage());
            }
        }
    }

    public function chunk(Collection $user_relation, Model $dynamic_small_config)
    {
        $parallel = new Parallel(5);
        foreach ($user_relation as $user) {
            $parallel->add(function () use ($user, $dynamic_small_config) {
                $day_small_income = $this->dsis->findSmallIncome([
                    'user_id' => $user->user_id,
                    'day' => $this->day,
                    'coin_symbol' => $dynamic_small_config->coin_symbol
                ]);
                if ($day_small_income) {
                    return;
                }
                //查找用户下一级用户
                $first_distributor_ids = $this->urs->findUserList([
                    'user_id' => [
                        'condition' => 'function',
                        'data' => function ($query) use ($user) {
                            $query->whereIn('user_id', $user->child_user_ids)->where('depth', $user->depth + 1);
                        }
                    ]
                ]);
                //计算下一级用户的团队业绩
                $first_distributor_team_assets = $this->uas->userAssetsList([
                    'select' => ['user_id', Db::raw('(assets + child_assets) as total_assets')],
                    'user_id' => function ($query) use ($first_distributor_ids) {
                        $query->whereIn('user_id', $first_distributor_ids->pluck("user_id")->toArray());
                    },
                    'coin_symbol' => $dynamic_small_config->coin_symbol,
                    'order' => 'total_assets desc'
                ]);
                $dynamic_small_area_num = '0';
                //计算小区总业绩
                foreach ($first_distributor_team_assets as $key => $team_assets) {
                    if ($key == 0) {//忽略大区
                        continue;
                    }
                    $dynamic_small_area_num = bcadd($dynamic_small_area_num, (string)$team_assets->total_assets);
                }
                $small_income = bcmul(
                    $dynamic_small_area_num,
                    bcdiv((string)$dynamic_small_config->percent, '100')
                );
                if (bccomp($small_income, '0') <= 0) {//小区没有收益
                    return;
                }
                $dynamic_small_income = $this->dsis->createIncome([
                    'user_id' => $user->user_id,
                    'day' => $this->day,
                    'coin_symbol' => $dynamic_small_config->coin_symbol,
                    'status' => 1,
                    'small_num' => $dynamic_small_area_num,
                    'small_income' => $small_income
                ]);
                $reward_status = $this->dsis->sendReward(
                    $user->user_id,
                    (string)$dynamic_small_config->coin_symbol,
                    $small_income
                );
                if ($reward_status == true) {
                    $dynamic_small_income->status = 2;
                    $this->dsis->updateIncome([
                        'id' => $dynamic_small_income->id,
                    ], [
                        'status' => $dynamic_small_income->status
                    ]);
                }
                $this->output->writeln(sprintf(
                    "user_id: %s, small_num: %s, small_income: %s, status: %s",
                    $user->user_id,
                    $dynamic_small_area_num,
                    $small_income,
                    $dynamic_small_income->status
                ));
            });
        }
        try {
            $parallel->wait();
        } catch (ParallelExecutionException $e) {
            $this->output->writeln($e->getMessage());
        }
    }
}
