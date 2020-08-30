<?php

namespace App\Services\Http;

use GuzzleHttp\Client;
use Hyperf\Guzzle\HandlerStackFactory;

class GuzzleService
{

    /**
     * @param array $config
     * @return Client
     */
    public function create()
    {
        return make(Client::class, []);
    }
}
