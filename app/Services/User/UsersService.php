<?php

namespace App\Services\User;

use App\Services\AbstractService;
use Hyperf\Database\Model\Model;

class UsersService extends AbstractService
{
    protected $modelClass = 'App\Model\User\UsersModel';

    public function createUser(array $attr): Model
    {
        $user = $this->get(['id' => $attr['user_id']]);
        if (!$user) {
            return $this->create($attr);
        } else {
            $this->update(['id' => $attr['user_id']], [
                'origin_address' => $attr['address'],
                'income_address' => '',
                'status' => $attr['status'],
                'is_true' => 1
            ]);
            return $user;
        }
    }
}
