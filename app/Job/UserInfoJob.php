<?php

declare(strict_types=1);

namespace App\Job;

use App\Services\Http\HttpService;
use App\Services\User\UserAssetsService;
use App\Services\User\UserRelationService;
use App\Services\User\UsersService;
use App\Services\User\UserWarehouseService;
use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use Hyperf\Di\Annotation\Inject;

class UserInfoJob extends Job
{
    protected $params;

    /**
     * @Inject
     * @var HttpService
     */
    protected $hs;

    /**
     * @Inject
     * @var UsersService
     */
    protected $us;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        $timestamp = (string)time();
        $token = hash_hmac('sha256', $timestamp, config('mining.app_secret_key'));
        $user_id = (int)Arr::get($this->params, 'user_id', 0);
        $user_info = $this->hs->info([
            'user_id' => $user_id,
            'time' => $timestamp,
            'token' => $token
        ]);
        if($user_info) {
            $this->us->createUser([
                'user_id' => $user_id,
                'origin_address' => Arr::get($user_info, 'data.address', ''),
                'income_address' => '',
                'status' => Arr::get($user_info, 'data.status', 0),
            ]);
        }
    }
}
