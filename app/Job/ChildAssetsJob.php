<?php

declare(strict_types=1);

namespace App\Job;

use App\Services\User\UserAssetsService;
use App\Services\User\UserRelationService;
use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use Hyperf\Di\Annotation\Inject;

class ChildAssetsJob extends Job
{
    protected $params;

    /**
     * @Inject
     * @var UserRelationService
     */
    protected $urs;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        $user_id = (int)Arr::get($this->params, 'user_id', 0);
        $coin_symbol = (string)Arr::get($this->params, 'coin_symbol', '');
        $value = (string)Arr::get($this->params, 'value', '0');
        if ($user_id == 0) {
            return;
        }
        $parent_ids = $this->urs->findUser($user_id)->parent_user_ids ?? [];
        $parallel = new Parallel(5);
        foreach ($parent_ids as $user_id) {
            $parallel->add(function () use ($user_id, $coin_symbol, $value) {
                try {
                    $uas = new UserAssetsService();
                    $uas->childUserAssets($user_id, $coin_symbol, $value);
                } catch (\Throwable $e) {
                    echo $e->getMessage() . PHP_EOL;
                }
            });
        }
        try {
            $parallel->wait();
        } catch (ParallelExecutionException $e) {
            throw $e;
        }
    }
}
