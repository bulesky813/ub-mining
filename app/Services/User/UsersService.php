<?php

namespace App\Services\User;

use App\Services\AbstractService;
use Hyperf\Database\Model\Model;

class UsersService extends AbstractService
{
    protected $modelClass = 'App\Model\User\UsersModel';

    public function createUser(array $attr): Model
    {
        $user = $this->get(['id' => $attr['id']]);
        if (!$user) {
            return $this->create($attr);
        } else {
            $this->update(['id' => $attr['id']], [
                'origin_address' => $attr['origin_address'],
                'income_address' => '',
                'status' => $attr['status'],
                'is_true' => 1
            ]);
            return $user;
        }
    }

    public function findUserList(array $attr)
    {
        return $this->findByAttr($attr);
    }
}
