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
        $user = $urs->create([
            'user_id' => $user_id,
            'parent_id' => $parent_id,
            'depth' => 0,
            'parent_user_ids' => [$parent_id],
            'child_user_ids' => []
        ]);
        return $this->success($user->toArray());
    }
}
