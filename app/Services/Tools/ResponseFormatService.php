<?php

namespace App\Services\Tools;

use App\Services\AbstractService;
use App\Services\Base\BaseRedisService;

class ResponseFormatService extends AbstractService
{
    public function userControllerStaticIncome(array $data): array
    {
        foreach ($data as &$item) {
            $item['num'] = bcmul($item['num'], '1', 2);
            $item['percent'] = bcmul($item['percent'] * 100, '1', 2);
            $item['today_income'] = bcmul($item['today_income'], '1', 2);
        }
        return $data;
    }

    public function userControllerWarehouse(array $data): array
    {
        foreach ($data as &$item) {
            $item['assets'] = bcmul($item['assets'], '1', 2);
            $item['income_info']->total_income = bcmul($item['income_info']->total_income, '1', 2);
            $item['income_info']->yesterday_income = bcmul($item['income_info']->yesterday_income, '1', 2);
        }
        return $data;
    }

    public function userControllerUserTeamList(array $data)
    {
        foreach ($data as &$item) {
            $item['user_assets'] = bcmul($item['user_assets'], '1', 2);
            $item['total_team_num'] = bcmul($item['total_team_num'], '1', 2);
            $item['total_big_area_num'] = bcmul($item['total_big_area_num'], '1', 2);
            $item['total_small_area_num'] = bcmul($item['total_small_area_num'], '1', 2);
        }
        return $data;
    }

    public function userControllerUserMyTeam(array $data)
    {
        foreach ($data as &$item) {
            $item = bcmul($item, '1', 2);
        }
        return $data;
    }
}
