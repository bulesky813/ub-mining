<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Base\AbstractCommand;
use App\Services\Income\DynamicBigIncomeConfigService;
use App\Services\Mine\MinePoolService;
use App\Services\Mine\MineService;
use App\Services\Queue\QueueService;
use App\Services\Separate\SeparateWarehouseService;
use App\Services\Income\StaticIncomeService;
use App\Services\User\UserAssetsService;
use App\Services\User\UserRelationService;
use App\Services\User\UserWarehouseService;
use Carbon\Carbon;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Database\Model\Collection;
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
class DynamicBigArea extends AbstractCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $signature = 'cmd:dynamic_big_area';

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
     * @var DynamicBigIncomeConfigService
     */
    protected $dbcs;

    /**
     * @Inject
     * @var UserAssetsService
     */
    protected $uas;

    /**
     * @var Collection
     */
    protected $dynamic_big_configs;

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
        $pools = $this->mps->mineList(['status', 1]); //查询启用的矿池
        foreach ($pools as $pool) {
            $this->dynamic_big_configs = $this->dbcs->getConfig([
                'config_id' => 0,
                'coin_symbol' => $pool->coin_symbol
            ]);
            $this->dynamic_big_configs = $this->dynamic_big_configs->sortByDesc("sort");
            //至少要两个一级分销商才有动态收益
            $this->urs->findUserList([
                'child_user_ids' => [
                    'condition' => 'function',
                    'data' => function ($query) {
                        $query->whereRaw('json_length(child_user_ids) > 1');
                    }
                ],
                'chunk' => [$this, 'chunk']
            ]);
        }
    }

    public function chunk(Collection $user_relation)
    {
        $parallel = new Parallel(5);
        foreach ($user_relation as $user) {
            $parallel->add(function () use ($user) {
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
                    'order' => 'total_assets desc'
                ]);
                //计算小区总业绩
                $small_area_assets = '0';
                $big_area_user_id = 0;
                foreach ($first_distributor_team_assets as $key => $team_assets) {
                    if ($key == 0) {
                        $big_area_user_id = $team_assets->user_id;
                        continue;
                    }
                    $small_area_assets = bcadd($team_assets->total_assets, $small_area_assets);
                }
                $config = $this->dynamic_big_configs->first(function ($dynamic_big_config) use ($small_area_assets) {
                    if ($dynamic_big_config->num < $small_area_assets) {
                        return true;
                    }
                    return false;
                });
                $dynamic_income = $this->uas->userAssetsList([
                    'user_id' => [
                        'condition' => 'in',
                        'data' => array_merge(
                            $first_distributor_ids->firstWhere("user_id", $big_area_user_id)
                                ->child_user_ids,
                            [$big_area_user_id]
                        )
                    ],
                    'order' => 'assets desc',
                    'paginate' => true,
                    'pn' => 0,
                    'ps' => $config->person_num
                ]);
                $dynamic_income_num = bcmul(
                    (string)$dynamic_income->sum("assets"),
                    bcdiv($config->percent, '100')
                );//动态大区收益
            });
        }
        try {
            $parallel->wait();
        } catch (ParallelExecutionException $e) {
            $this->output->writeln($e->getMessage());
        }
    }
}
