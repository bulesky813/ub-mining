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
            $dynamic_big_config = $this->dbcs->getConfig([
                'config_id' => 0,
                'coin_symbol' => $pool->coin_symbol
            ]);
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
                $first_distributor_ids = $this->urs->findUserList([
                    'user_id' => [
                        'condition' => 'function',
                        'data' => function ($query) use ($user) {
                            $query->whereIn('user_id', $user->child_user_ids)->where('depth', $user->depth + 1);
                        }
                    ]
                ])
                    ->pluck("user_id")
                    ->toArray();
                $first_distributor_team_assets = $this->uas->userAssetsList([
                    'select' => ['user_id', Db::raw('(assets + child_assets) as total_assets')],
                    'user_id' => function ($query) use ($first_distributor_ids) {
                        $query->whereIn('user_id', $first_distributor_ids);
                    }
                ])->toArray();
                var_dump($first_distributor_team_assets);
            });
        }
        try {
            $parallel->wait();
        } catch (ParallelExecutionException $e) {
            $this->output->writeln($e->getMessage());
        }
    }
}
