<?php

namespace App\Services\User;

use App\Services\AbstractService;
use Hyperf\Database\Model\Model;

class UserRelationService extends AbstractService
{
    protected $modelClass = 'App\Model\User\UserRelationModel';

    public function bind(int $user_id, int $parent_id): Model
    {
        $parent_user = $this->get(['user_id' => $parent_id]);
        if ($parent_user) {
            $parent_user->child_user_ids = array_merge($parent_user->child_user_ids, [$user_id]);
            $parent_user->save();
            foreach ($parent_user->parent_user_ids as $grandpa_user_id) {
                $grandpa_user = $this->get(['user_id' => $grandpa_user_id]);
                if ($grandpa_user) {
                    $grandpa_user->child_user_ids = array_merge($grandpa_user->child_user_ids, [$user_id]);
                    $grandpa_user->save();
                }
            }
            $pids = array_merge($parent_user->parent_user_ids, [$parent_id]);
        } else {
            $parent_user = $this->create([
                'user_id' => $parent_id,
                'parent_id' => 0,
                'depth' => 0,
                'parent_user_ids' => [],
                'child_user_ids' => [$user_id]
            ]);
            $pids = [$parent_user->user_id];
        }
        $user = $this->create([
            'user_id' => $user_id,
            'parent_id' => $parent_id,
            'depth' => count($pids),
            'parent_user_ids' => $pids,
            'child_user_ids' => []
        ]);
        return $user;
    }
}
