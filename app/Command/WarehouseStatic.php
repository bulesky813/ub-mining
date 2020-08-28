<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Base\AbstractCommand;
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
use Hyperf\Logger\Logger;
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

    protected $separate_warehouse = null;

    protected $day = null;

    /**
     * @var UserWarehouseService|null
     */
    protected $uws = null;

    /**
     * @var StaticIncomeService|null
     */
    protected $sis = null;

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
        $this->sis = new StaticIncomeService();
        $this->uws = new UserWarehouseService();
        $this->day = Carbon::now()->format('Y-m-d');
        $pools = $mps->mineList(['status', 1]); //查询启用的矿池
        foreach ($pools as $pool) {
            $this->separate_warehouse = $sws->separateWarehouse($pool->coin_symbol);//查询币种的分仓信息
            $uas->userAssetsList([
                'coin_symbol' => $pool->coin_symbol,
                'assets' => [
                    'condition' => 'function',
                    'data' => function ($query) {
                        $query->where('assets', '>', 0);
                    }
                ],
                'chunk' => [$this, 'chunk']
            ]);
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
                        $percent = $this->separate_warehouse->offsetGet($max_warehouse_sort - 1)->percent ?? 0;
                        $percent = bcdiv($percent, '100');
                        //发放静态收益
                        $this->sis->createIncome([
                            'user_id' => $user->user_id,
                            'coin_symbol' => $user->coin_symbol,
                            'day' => $this->day,
                            'num' => $user->assets,
                            'percent' => $percent,
                            'today_income' => bcmul($percent, (string)$user->assets),
                            'status' => 1
                        ]);
                        $this->output->writeln(sprintf(
                            "user_id: %s, num: %s, percent: %s",
                            $user->user_id,
                            $user->assets,
                            $percent
                        ));
                    }
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
