<?php

declare(strict_types=1);

namespace App\Job;

use App\Services\User\UserAssetsService;
use App\Services\User\UserRelationService;
use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;

class ChildAssetsJob extends Job
{
    protected $params;

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
        $urs = new UserRelationService();
        $parent_ids = $urs->findUser($user_id)->parent_user_ids ?? [];
        try {
            $parallel = new Parallel(5);
            foreach ($parent_ids as $user_id) {
                $parallel->add(function () use ($user_id, $coin_symbol, $value) {
                    $uas = new UserAssetsService();
                    $uas->childUserAssets($user_id, $coin_symbol, $value);
                });
            }
            try {
                $parallel->wait();
            } catch (ParallelExecutionException $e) {
                throw $e;
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
