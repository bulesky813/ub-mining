<?php

namespace App\Services\Base;

use App\Services\Http\HttpService;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;
use Hyperf\Di\Annotation\Inject;

trait BaseRewardService
{
    public function sendReward(int $user_id, string $coin_symbol, string $change): bool
    {
        $timestamp = (string)time();
        $token = hash_hmac('sha256', $timestamp, config('mining.app_secret_key'));
        try {
            $hs = new HttpService();
            $reward_status = $hs->reward([
                'time' => $timestamp,
                'uid' => $user_id,
                'change' => $change,
                'coin_symbol' => $coin_symbol,
                'token' => $token
            ]);
            return $reward_status;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
