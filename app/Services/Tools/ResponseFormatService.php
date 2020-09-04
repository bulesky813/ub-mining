<?php

namespace App\Services\Tools;

use App\Services\AbstractService;
use App\Services\Base\BaseRedisService;

class ResponseFormatService extends AbstractService
{
    public function userControllerStaticIncome(array $data): array
    {
        foreach ($data as &$item) {
            $item['num'] = sprintf("%.2f", $item['num']);
            $item['percent'] = sprintf("%.2f", $item['percent'] * 100);
            $item['today_income'] = sprintf("%.2f", $item['today_income'] * 100);
        }
        return $data;
    }

    public function userControllerWarehouse(array $data): array
    {
        foreach ($data as &$item) {
            $item['assets'] = sprintf("%.2f", $item['assets']);
            $item['income_info']->total_income = sprintf("%.2f", $item['income_info']->total_income);
            $item['income_info']->yesterday_income = sprintf("%.2f", $item['income_info']->yesterday_income);
        }
        return $data;
    }

    public function userControllerUserTeamList(array $data)
    {
        foreach ($data as &$item) {
            $item['user_assets'] = sprintf("%.2f", $item['user_assets']);
            $item['total_team_num'] = sprintf("%.2f", $item['total_team_num']);
            $item['total_big_area_num'] = sprintf("%.2f", $item['total_big_area_num']);
            $item['total_small_area_num'] = sprintf("%.2f", $item['total_small_area_num']);
        }
        return $data;
    }

    public function userControllerUserMyTeam(array $data)
    {
        foreach ($data as &$item) {
            $item = sprintf("%.2f", $item);
        }
        return $data;
    }
}
