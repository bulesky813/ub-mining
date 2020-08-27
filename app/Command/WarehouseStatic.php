<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Base\AbstractCommand;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;

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
    }
}
