<?php

namespace App\Services\Admin;

use App\Services\AbstractService;

class AdminUsersService extends AbstractService
{
    protected $modelClass = 'App\Model\Admin\AdminUsersModel';

    const ADMIN_USER_LOGIN_TOKEN = 'admin_user_login_';

    public function login($params)
    {
        try {
            $where['user_name'] = $params['user_name'];
            $where['password'] = md5($params['password']);
            $where['status'] = 1;
            $user = $this->get($where);
            if (!$user) {
                throw new \Exception('用户名或密码错误');
            }

            $token = md5(uniqid().time().$user->id);
            $value = json_encode([
                'token' => $token,
                'user_id' => $user->id,
            ]);
            $this->redis()->setex(self::ADMIN_USER_LOGIN_TOKEN.$token, 86400, $value);

            return ['token' => $token];
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function loginOut($token)
    {
        try {
            $this->redis()->del(self::ADMIN_USER_LOGIN_TOKEN.$token);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function adminCreate()
    {
    }
}
