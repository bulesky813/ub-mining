<?php

namespace App\Services\User;

use App\Services\AbstractService;
use Hyperf\Database\Model\Model;

class UserAssetsService extends AbstractService
{
    protected $modelClass = 'App\Model\User\UserAssetsModel';

    public function userAssets(int $user_id, string $coin_symbol, string $value): Model
    {
        $assets = $this->get(['user_id' => $user_id, 'coin_symbol' => $coin_symbol]);
        if ($assets) {
            $assets->increment('assets', $value);
        } else {
            $assets = $this->create([
                'user_id' => $user_id,
                'coin_symbol' => $coin_symbol,
                'assets' => $value,
                'child_assets' => 0
            ]);
        }
        return $assets;
    }
}
