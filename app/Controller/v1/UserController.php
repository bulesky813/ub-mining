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

class UserController extends AbstractController
{
    public function relation(UserRelationRequest $request)
    {
        $user_id = $request->input('user_id', 0);
        $parent_id = $request->input('parent_id', 0);
        return $this->success([
            'user_id' => $user_id,
            'parent_id' => $parent_id
        ]);
    }
}
