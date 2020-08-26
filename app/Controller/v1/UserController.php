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
        $user_id = (int)$request->input('user_id', 0);
        $parent_id = (int)$request->input('parent_id', 0);
        $user = $urs->get(['user_id' => $user_id]);
        if ($user) {
            return $this->error('用户已绑定!');
        }
        Db::beginTransaction();
        try {
            $user = $urs->bind($user_id, $parent_id);
            Db::commit();
            return $this->success($user->toArray());
        } catch (\Throwable $e) {
            Db::rollBack();
            return $this->error($e->getMessage());
        }
    }
}
