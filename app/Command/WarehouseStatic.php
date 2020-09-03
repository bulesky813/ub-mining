<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Base\AbstractCommand;
use App\Services\Http\HttpService;
use App\Services\Income\ExcludeRewardsUsersService;
use App\Services\Income\IncomeStatisticsService;
use App\Services\Mine\MinePoolService;
use App\Services\Mine\MineService;
use App\Services\Queue\QueueService;
use App\Services\Separate\SeparateWarehouseService;
use App\Services\Income\StaticIncomeService;
use App\Services\User\UserAssetsService;
use App\Services\User\UserWarehouseService;
use Carbon\Carbon;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Database\Model\Collection;
use Hyperf\Logger\Logger;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * @Command
 */
class WarehouseStatic extends AbstractCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $signature = 'cmd:warehouse_static';

    /**
     * @var null|Collection
     */
    protected $separate_warehouse = null;

    protected $day = null;

    /**
     * @var UserWarehouseService|null
     */
    protected $uws = null;

    /**
     * @Inject
     * @var StaticIncomeService
     */
    protected $sis = null;

    /**
     * @Inject
     * @var IncomeStatisticsService
     */
    protected $iss;

    /**
     * @Inject
     * @var QueueService
     */
    protected $qs;

    /**
     * @Inject
     * @var ExcludeRewardsUsersService
     */
    protected $erus;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct();
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('持仓静态收益发放脚本');
    }

    public function handle()
    {
        $uas = new UserAssetsService();
        $mps = new MinePoolService();
        $sws = new SeparateWarehouseService();
        $this->uws = new UserWarehouseService();
        $this->day = Carbon::now()->format('Y-m-d');
        $pools = $mps->mineList(['status' => 1]); //查询启用的矿池
        foreach ($pools as $pool) {
            $exclude_user_ids = Arr::get(
                $this->erus->excludeUsersGet(['coin_symbol' => $pool->coin_symbol]),
                'user_ids',
                []
            );
            $this->output->writeln(sprintf("exclude user_id: %s", implode(",", $exclude_user_ids)));
            $this->separate_warehouse = $sws->separateWarehouse($pool->coin_symbol);//查询币种的分仓信息
            $uas->findAssetsList([
                'user_id' => $exclude_user_ids ? function ($query) use ($exclude_user_ids) {
                    $query->whereNotIn('user_id', $exclude_user_ids);
                } : 0,
                'coin_symbol' => $pool->coin_symbol,
                'assets' => [
                    'condition' => 'function',
                    'data' => function ($query) {
                        $query->where('assets', '>', 0);
                    }
                ],
                'chunk' => [$this, 'chunk']
            ]);
            try {
                $sum_income = $this->sis->sumIncome([
                    'today_static_income' => 'today_income',
                    'total_lock' => 'num'
                ], [
                    'day' => $this->day,
                    'coin_symbol' => $pool->coin_symbol
                ]);
                $yesterday_statistics = $this->iss->findStatistics([
                    'day' => Carbon::yesterday()->format('Y-m-d'),
                    'coin_symbol' => $pool->coin_symbol
                ]);
                $this->iss->createStatistics([
                    'day' => $this->day,
                    'coin_symbol' => $pool->coin_symbol,
                    'static_income_num' => $sum_income->today_static_income,
                    'diff_yesterday' => bcsub(
                        (string)$sum_income->total_lock,
                        (string)$yesterday_statistics ? $yesterday_statistics->total_lock : '0'
                    ),
                    'total_lock' => $sum_income->total_lock,
                ]);
            } catch (\Throwable $e) {
                $this->output->writeln($e->getMessage());
            }
        }
    }

    public function chunk($assets_users)
    {
        $parallel = new Parallel(5);
        foreach ($assets_users as $user) {
            $parallel->add(function () use ($user) {
                try {
                    $static_income = $this->sis->findStaticIncome([
                        'user_id' => $user->user_id,
                        'coin_symbol' => $user->coin_symbol,
                        'day' => $this->day
                    ]);
                    if (!$static_income) {
                        $max_warehouse_sort = $this->uws->maxWarehouseSort($user->user_id, $user->coin_symbol); //获取最大持币
                        $percent = bcdiv(
                            $this->separate_warehouse->get($max_warehouse_sort - 1)->percent ?? '0',
                            '100'
                        );
                        $today_income = bcmul($percent, (string)$user->assets);
                        //记录静态收益
                        $static_income = $this->sis->createIncome([
                            'user_id' => $user->user_id,
                            'coin_symbol' => $user->coin_symbol,
                            'day' => $this->day,
                            'num' => $user->assets,
                            'percent' => $percent,
                            'today_income' => $today_income,
                            'status' => 1
                        ]);
                    }
                    if ($static_income->status == 1) {
                        $reward_status = $this->sis->sendReward(
                            $user->user_id,
                            $user->coin_symbol,
                            (string)$static_income->today_income
                        );
                    } else {
                        return;
                    }
                    if ($reward_status == true) {
                        $static_income->status = 2;
                        $this->sis->updateIncome([
                            'id' => $static_income->id
                        ], [
                            'status' => $static_income->status
                        ]);
                        $this->qs->incomeInfo([
                            'user_id' => $user->user_id,
                            'coin_symbol' => $user->coin_symbol,
                            'percent' => $static_income->percent
                        ]);
                    }
                    $this->output->writeln(sprintf(
                        "user_id: %s, coin_symbol: %s, num: %s, percent: %s, status: %s",
                        $user->user_id,
                        $user->coin_symbol,
                        $user->assets,
                        $static_income->percent,
                        $static_income->status
                    ));
                } catch (\Throwable $e) {
                    $this->output->writeln(sprintf(
                        "user_id: %s, error: %s",
                        $user->user_id,
                        $e->getMessage()
                    ));
                }
            });
        }
        try {
            $parallel->wait();
        } catch (ParallelExecutionException $e) {
            $this->output->writeln($e->getMessage());
        }
    }
}
