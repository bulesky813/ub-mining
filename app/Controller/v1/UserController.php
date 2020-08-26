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
use App\Request\User\UserRelationRequest;
use App\Services\User\UserRelationService;
use Hyperf\DbConnection\Db;

class UserController extends AbstractController
{
    public function relation(UserRelationRequest $request, UserRelationService $urs)
    {
        $user_id = $request->input('user_id', 0);
        $parent_id = $request->input('parent_id', 0);
        $user = $urs->get(['user_id' => $user_id]);
        if ($user) {
            return $this->error('用户已绑定!');
        }
        Db::beginTransaction();
        try {
            $parent_user = $urs->get(['user_id' => $parent_id]);
            if (!$parent_user) {
                $urs->update(['user_id' => $parent_id], [
                    'child_user_ids' => array_merge($parent_user->child_user_ids, [$user_id])
                ]);
                foreach ($parent_user->parent_user_ids as $grandpa_user_id) {
                    $grandpa_user = $urs->get(['user_id' => $grandpa_user_id]);
                    if ($grandpa_user) {
                        $urs->update(['user_id', $grandpa_user_id], [
                            'child_user_ids' => array_merge($grandpa_user->child_user_ids, [$user_id])
                        ]);
                    }
                }
                $pids = array_merge($parent_user->parent_user_ids, [$parent_id]);
            } else {
                $parent_user = $urs->create([
                    'user_id' => $parent_id,
                    'parent_id' => 0,
                    'depth' => 0,
                    'parent_user_ids' => [],
                    'child_user_ids' => [$user_id]
                ]);
                $pids = [$parent_id];
            }
            $user = $urs->create([
                'user_id' => $user_id,
                'parent_id' => $parent_id,
                'depth' => 0,
                'parent_user_ids' => $pids,
                'child_user_ids' => []
            ]);
            Db::commit();
            return $this->success($user->toArray());
        } catch (\Throwable $e) {
            Db::rollBack();
            return $this->error($e->getMessage());
        }
    }
}
