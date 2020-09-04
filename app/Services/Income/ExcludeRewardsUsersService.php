<?php

namespace App\Services\Income;

use App\Services\AbstractService;
use Hyperf\Database\Model\Model;
use App\Model\Income\ExcludeRewardsUsersModel;

class ExcludeRewardsUsersService extends AbstractService
{
    protected $modelClass = 'App\Model\Income\ExcludeRewardsUsersModel';

    public function excludeUsersCreate($params)
    {
        try {
            if (!isset($params['user_ids']) || empty($params['user_ids'])) {
                (new ExcludeRewardsUsersModel)->where([
                    'coin_symbol' => $params['coin_symbol']
                ])->delete();
                return true;
            }
            if (!is_array($params['user_ids'])) {
                $params['user_ids'] = explode(',', $params['user_ids']);
            }
            foreach ($params['user_ids'] as $v) {
                if ($v <= 0) {
                    throw new \Exception('排除奖励ID必须大于0');
                    break;
                }
            }
            $data = $this->findByAttr([
                'coin_symbol' => $params['coin_symbol'],
                'user_id' => $params['user_ids']
            ]);
            $result = [];
            if ($data) {
                $user = array_column($data->toArray(), 'user_id');
                $result = array_diff($params['user_ids'], $user);
                $result2 = array_diff($user, $params['user_ids']);
            }
            if (!empty($result) || !empty($result2)) {
                (new ExcludeRewardsUsersModel)->where([
                    'coin_symbol' => $params['coin_symbol']
                ])->delete();
                foreach ($params['user_ids'] as $k => $v) {
                    $data = $this->create([
                        'coin_symbol' => $params['coin_symbol'],
                        'user_id' => $v,
                    ]);
                }
            }
            return true;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function excludeUsersGet($params)
    {
        try {
            $data = $this->findByAttr([
                'coin_symbol' => $params['coin_symbol'],
//                'user_id' => $params['user_ids']
            ]);
            if ($data) {
                return ['user_ids' => array_column($data->toArray(), 'user_id')];
            } else {
                return [];
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
