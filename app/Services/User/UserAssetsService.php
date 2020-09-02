<?php

namespace App\Services\User;

use App\Services\AbstractService;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

class UserAssetsService extends AbstractService
{
    protected $modelClass = 'App\Model\User\UserAssetsModel';

    /**
     * @Inject
     * @var UserRelationService
     */
    protected $urs;

    public function userAssets(int $user_id, string $coin_symbol, string $value): Model
    {
        $assets = $this->get(['user_id' => $user_id, 'coin_symbol' => $coin_symbol]);
        if ($assets) {
            $assets->increment('assets', $value);
        } else {
            $assets = $this->create([
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'assets' => $value,
                'child_assets' => 0
            ]);
        }
        return $assets;
    }

    public function childUserAssets(int $user_id, string $coin_symbol, string $value)
    {
        $assets = $this->get(['user_id' => $user_id, 'coin_symbol' => $coin_symbol]);
        if ($assets) {
            $assets->increment('child_assets', $value);
        } else {
            $assets = $this->create([
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'assets' => 0,
                'child_assets' => $value
            ]);
        }
        return $assets;
    }

    public function findUserAssets(int $user_id, string $coin_symbol): ?Model
    {
        return $this->get([
            'user_id' => $user_id,
            'coin_symbol' => $coin_symbol
        ]);
    }

    public function findAssetsList($attr)
    {
        return $this->findByAttr($attr);
    }

    public function findAreaAssets(int $user_id, string $coin_symbol)
    {
        $user_relation = $this->urs->get(['user_id' => $user_id]);
        $first_distributor_ids = $this->urs->findUserList([
            'user_id' => function ($query) use ($user_relation) {
                $query->whereIn('user_id', $user_relation->child_user_ids);
            },
            'depth' => $user_relation->depth + 1
        ]);
        if ($first_distributor_ids->isEmpty()) {
            return [
                'total_big_area_num' => '0',
                'total_small_area_num' => '0'
            ];
        }
        $child_assets_list = $this->findAssetsList([
            'select' => [
                'user_id',
                'assets',
                Db::raw("(assets + child_assets) as total_assets")
            ],
            'user_id' => function ($query) use ($first_distributor_ids) {
                $query->whereIn('user_id', $first_distributor_ids->pluck("user_id")->toArray());
            },
            'coin_symbol' => $coin_symbol
        ])->sortByDesc("total_assets");
        $big_area = $child_assets_list->first();
        $total_big_area_num = $big_area ? $big_area->total_assets : '0';
        return [
            'total_big_area_num' => $total_big_area_num,
            'total_small_area_num' => $child_assets_list->count() == 1
                ? '0' : bcsub((string)$child_assets_list->sum('total_assets'), (string)$total_big_area_num)
        ];
    }
}
