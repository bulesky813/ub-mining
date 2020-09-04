<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Controller\v1;

use App\Controller\AbstractController;
use App\Request\User\UserAiWarehouseRequest;
use App\Request\User\UserChangeAssetsRequest;
use App\Request\User\UserCoinSymbolInfoRequest;
use App\Request\User\UserRegisterRequest;
use App\Request\User\UserRelationRequest;
use App\Request\User\UserStaticIncomeRequest;
use App\Request\User\UserTeamListRequest;
use App\Request\User\UserWarehouseRequest;
use App\Request\User\UserWarehouseRecordRequest;
use App\Services\Income\DynamicSmallIncomeService;
use App\Services\Income\StaticIncomeService;
use App\Services\Queue\QueueService;
use App\Services\Separate\SeparateWarehouseService;
use App\Services\Tools\ResponseFormatService;
use App\Services\User\UserAssetsService;
use App\Services\User\UserRelationService;
use App\Services\User\UsersService;
use App\Services\User\UserWarehouseRecordService;
use App\Services\User\UserWarehouseService;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Arr;

class UserController extends AbstractController
{
    /**
     * @Inject
     * @var QueueService
     */
    protected $qs;

    /**
     * @Inject
     * @var UserWarehouseService
     */
    protected $uws;

    /**
     * @Inject
     * @var StaticIncomeService
     */
    protected $sis;

    /**
     * @Inject
     * @var UserRelationService
     */
    protected $urs;

    /**
     * @Inject
     * @var UserWarehouseRecordService
     */
    protected $uwrs;

    /**
     * @Inject
     * @var SeparateWarehouseService
     */
    protected $sws;

    /**
     * @Inject
     * @var UsersService
     */
    protected $us;

    /**
     * @Inject
     * @var UserAssetsService
     */
    protected $uas;

    /**
     * @Inject
     * @var DynamicSmallIncomeService
     */
    protected $dsis;

    /**
     * @Inject
     * @var ResponseFormatService
     */
    protected $rfs;

    public function relation(UserRelationRequest $request)
    {
        $user_id = (int)$request->input('user_id', 0);
        $parent_id = (int)$request->input('parent_id', 0);
        Db::beginTransaction();
        try {
            $user = $this->urs->bind($user_id, $parent_id);
            Db::commit();
            $this->qs->userInfo([
                'user_id' => $user_id
            ]);
            $this->qs->userInfo([
                'user_id' => $parent_id
            ]);
            return $this->success($user->toArray());
        } catch (\Throwable $e) {
            Db::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function register(UserRegisterRequest $request)
    {
        $user_id = $request->input('user_id');
        $origin_address = $request->input('origin_address');
        try {
            $user = $this->us->createUser([
                'id' => $user_id,
                'origin_address' => $origin_address,
                'status' => 10
            ]);
            return $this->success($user->toArray());
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function changeAssets(UserChangeAssetsRequest $request)
    {
        $user_id = (int)$request->input('user_id');
        $coin_symbol = (string)$request->input('coin_symbol');
        $separate_warehouse_sort = (int)$request->input('separate_warehouse_sort');
        $value = (string)$request->input('value');
        Db::beginTransaction();
        try {
            $user_warehouse = $this->uws->setUserWarehouse($user_id, $coin_symbol, $separate_warehouse_sort, $value);
            $user_warehouse_record = $this->uwrs->record([
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'sort' => $separate_warehouse_sort,
                'value_before' => bcsub((string)$user_warehouse->assets, $value),
                'num' => $value
            ]);
            $this->qs->childAssets([
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'value' => $value
            ]);
            Db::commit();
            return $this->success($user_warehouse_record->toArray());
        } catch (\Throwable $e) {
            Db::rollBack();
            return $this->error($e->getMessage());
        }
    }

    public function warehouse(UserWarehouseRequest $request)
    {
        $user_id = (int)$request->input('user_id');
        $coin_symbol = (string)$request->input('coin_symbol');
        try {
            $user_warehouses = $this->uws->userWarehouse($user_id, $coin_symbol, true);
            return $this->success($this->rfs->userControllerWarehouse($user_warehouses->toArray()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function staticIncome(UserStaticIncomeRequest $request)
    {
        $user_id = (int)$request->input('user_id');
        $coin_symbol = (string)$request->input('coin_symbol', '') ?: '';
        try {
            $user_static_incomes = $this->sis->listStaticIncome([
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'order' => 'created_at desc'
            ]);
            return $this->success($this->rfs->userControllerStaticIncome($user_static_incomes->toArray()));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 用户分仓记录列表查询
     * @param UserWarehouseRecordRequest $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getUserWarehouseRecord(UserWarehouseRecordRequest $request)
    {
        try {
            $params = $request->all();
            $data = $this->uwrs->getList($params);
            $data = $data->toArray();
            return $this->success($data);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function userCoinSymbolInfo(UserCoinSymbolInfoRequest $request)
    {
        try {
            $user_id = $request->input('user_id');
            $coin_symbol = $request->input('coin_symbol');
            $userWarehouseAssets = $this->uws->sumAssets([
                'total_assets' => 'assets',
                'yesterday_income' => "income_info->'$.yesterday_income'",
                'total_income' => "income_info->'$.total_income'"
            ], [
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol
            ]);
            $userWarehouseAssets = $userWarehouseAssets->toArray();
            $userWarehouseAssets['dynamic_income'] = $this->dsis->sumIncome(
                [
                    'dynamic_income' => 'small_income'
                ],
                [
                    'user_id' => $user_id,
                    'coin_symbol' => $coin_symbol
                ]
            )->dynamic_income;
            foreach ($userWarehouseAssets as $key => $value) {
                $userWarehouseAssets[$key] = $value == 'null' || is_null($value)
                    ? 0 : bcmul((string)$value, '1', 2);
            }
            return $this->success($userWarehouseAssets);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function userAiWarehouse(UserAiWarehouseRequest $request)
    {
        $user_id = (int)$request->input('user_id');
        $coin_symbol = (string)$request->input('coin_symbol');
        $assets = (string)$request->input('assets');
        try {
            $separate_warehouse = $this->sws->separateWarehouse($coin_symbol);
            $user_warehouse = $this->uws->userWarehouse($user_id, $coin_symbol);
            $outputs = [];
            foreach ($separate_warehouse as $currency_separate_warehouse) {
                $user_currency_warehouse = $user_warehouse
                    ->firstWhere('sort', $currency_separate_warehouse->sort);
                if (!$user_currency_warehouse || $user_currency_warehouse->assets < $currency_separate_warehouse->high) {
                    $user_assets = $user_currency_warehouse ? $user_currency_warehouse->assets : '0';
                    $ai_assets = bcsub(
                        (string)$currency_separate_warehouse->high,
                        $user_assets
                    );
                    $change_assets = bccomp($assets, $ai_assets) < 0 ? $assets : $ai_assets;
                    if (bccomp(
                        bcadd($user_assets, $change_assets),
                        (string)$currency_separate_warehouse->low
                    ) <= 0) {
                        break;
                    }
                    $outputs[] = [
                        'coin_symbol' => $coin_symbol,
                        'sort' => $currency_separate_warehouse->sort,
                        'assets' => bcmul((string)$change_assets, '1', 2)
                    ];
                    $assets = bcsub($assets, $change_assets);
                    if (bccomp($assets, '0') <= 0) {
                        break;
                    }
                }
            }
            return $this->success($outputs);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage());
        }
    }

    public function userMyTeam(UserTeamListRequest $request)
    {
        $user_id = (int)$request->input('user_id');
        $coin_symbol = (string)$request->input('coin_symbol');
        $user_relation = $this->urs->findUser($user_id);
        $user_assets = $this->uas->findUserAssets($user_id, $coin_symbol);
        $output = [
            'total_address_num' => $user_relation ? count($user_relation->child_user_ids) + 1 : '0',
            'total_team_num' => count($user_relation->child_user_ids) + 1,
            'total_big_area_num' => '0',
            'total_small_area_num' => '0',
            'user_assets' => $user_assets ? $user_assets->assets : '0'
        ];
        if (count($user_relation->child_user_ids) == 0) {
            return $this->success($output);
        }
        if ($user_assets) {
            $output['total_team_num'] = bcadd($user_assets->assets, $user_assets->child_assets);
            $output['user_assets'] = $user_assets->assets;
        }
        [
            'total_big_area_num' => $output['total_big_area_num'],
            'total_small_area_num' => $output['total_small_area_num']
        ] = $this->uas->findAreaAssets(
            $user_id,
            $coin_symbol
        );
        return $this->success($this->rfs->userControllerUserMyTeam($output));
    }

    public function userTeamList(UserTeamListRequest $request)
    {
        $user_id = (int)$request->input('user_id');
        $coin_symbol = (string)$request->input('coin_symbol');
        $page_max_id = (int)$request->input('page_max_id');
        $user_relation = $this->urs->findUser($user_id);
        $output = [];
        if (count($user_relation->child_user_ids) == 0) {
            return $this->success($output);
        }
        $first_distributor_ids = $this->urs->findUserList([
            'user_id' => function ($query) use ($user_relation) {
                $query->whereIn('user_id', $user_relation->child_user_ids);
            },
            'depth' => $user_relation->depth + 1
        ])
            ->pluck("user_id")
            ->toArray();
        $child_assets_list = $this->uas->findAssetsList([
            'select' => [
                'user_id',
                'assets',
                Db::raw("(assets + child_assets) as total_assets")
            ],
            'user_id' => function ($query) use ($first_distributor_ids) {
                $query->whereIn('user_id', $first_distributor_ids);
            },
            'coin_symbol' => $coin_symbol
        ])->sortByDesc("total_assets");
        $sort_child_user_ids = $child_assets_list->pluck("user_id")->toArray(); //按资产排序过的user_id
        $child_user_ids = collect($sort_child_user_ids)
            ->merge(collect($first_distributor_ids)->diff($sort_child_user_ids));
        $child_user_ids = $child_user_ids
            ->slice(($child_user_ids->search($page_max_id) ?: -1) + 1, 20)
            ->toArray();
        $child_user_list = $this->us->findUserList([
            'with' => [
                'userRelation',
                'userAssets' => function ($query) use ($coin_symbol) {
                    $query->where('coin_symbol', $coin_symbol);
                }
            ],
            'user_id' => function ($query) use ($child_user_ids) {
                $query->whereIn('id', $child_user_ids);
            }
        ])->keyBy("id");
        foreach ($child_user_ids as $user_id) {
            $child_user = $child_user_list->get($user_id);
            $team_assets = [
                'user_id' => $child_user->id,
                'address' => $child_user->origin_address,
                'user_assets' => '0',
                'total_address_num' => count($child_user->userRelation->child_user_ids ?? []) + 1
            ];
            $user_coin_symbol_assets = $child_user->userAssets->first();
            if ($user_coin_symbol_assets) {
                $team_assets['user_assets'] = $user_coin_symbol_assets->assets;
                $team_assets['total_team_num'] = bcadd(
                    $user_coin_symbol_assets->assets,
                    $user_coin_symbol_assets->child_assets
                );
            } else {
                $team_assets['total_team_num'] = '0';
            }
            [
                'total_big_area_num' => $team_assets['total_big_area_num'],
                'total_small_area_num' => $team_assets['total_small_area_num']
            ] = $this->uas->findAreaAssets($user_id, $coin_symbol);
            $output[] = $team_assets;
        }
        return $this->success($this->rfs->userControllerUserTeamList($output));
    }

    public function adminRelation(RequestInterface $request)
    {
        $user_id = $request->input('user_id');
        $coin_symbol = (string)$request->input('coin_symbol');
        $user_relation = null;
        if (is_numeric($user_id)) {
            $user_relation = $this->urs->findUser((int)$user_id);
        } else {
            $user = $this->us->findUserList([
                'with' => ['userRelation'],
                'origin_address' => (string)$user_id
            ])->first();
            if ($user) {
                $user_relation = $user->userRelation;
                $user_id = $user->id;
            }
        }
        if (!$user_relation) {
            $this->success([]);
        }
        $child_user_list = $this->urs->findUserList([
            'with' => [
                'user',
                'assets' => function ($query) use ($coin_symbol) {
                    $query->where('coin_symbol', $coin_symbol);
                }
            ],
            'user_id' => function ($query) use ($user_relation, $user_id) {
                $query->whereIn('user_id', array_merge($user_relation->child_user_ids, [$user_id]));
            },
            'order' => 'depth asc'
        ]);
        $children = [];
        $key_hash = [];
        $child_user_list->each(function ($child_user, $key) use (&$children, &$key_hash, $user_id) {
            if ($child_user->user_id == $user_id) {
                $children = [
                    $user_id => [
                        'id' => $user_id,
                        'label' => $child_user->user->origin_address ?? '',
                        'assets' => $child_user->assets->first()->assets ?? '0',
                        'children' => []
                    ]
                ];
                $key_hash[$child_user->user_id] = "{$child_user->user_id}.children";
            } else {
                $key_hash[$child_user->user_id] = $key_hash[$child_user->parent_id] . ".{$child_user->user_id}.children";
                $parent_user = Arr::get($children, $key_hash[$child_user->parent_id], []);
                $parent_user[$child_user->user_id] = [
                    'id' => $child_user->user_id,
                    'label' => $child_user->user->origin_address ?? '',
                    'assets' => $child_user->assets->first()->assets ?? '0',
                    'children' => [],
                ];
                Arr::set($children, $key_hash[$child_user->parent_id], $parent_user);
            }
        });
        $children = array_format($children);
        $output = [
            'total_big_area_num' => '0',
            'total_small_area_num' => '0',
            'children' => $children[0]
        ];
        [
            'total_big_area_num' => $output['total_big_area_num'],
            'total_small_area_num' => $output['total_small_area_num']
        ] = $this->uas->findAreaAssets((int)$user_id, $coin_symbol);
        return $this->success($output);
    }
}
