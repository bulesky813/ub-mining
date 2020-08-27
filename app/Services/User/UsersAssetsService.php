<?php

namespace App\Services\User;

use App\Services\AbstractService;
use Hyperf\Database\Model\Model;

class UsersAssetsService extends AbstractService
{
    protected $modelClass = 'App\Model\User\UserAssetsModel';

    public function userAssets(int $user_id, $coin_symbol, string $value)
    {
        $assets = $this->get(['user_id' => $user_id, 'coin_symbol' => $coin_symbol]);
        if ($assets) {
            $assets->increment('assets', $value);
        } else {
            $this->create([
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'assets' => $value,
                'child_assets' => 0
            ]);
        }
    }
}
