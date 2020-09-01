<?php

namespace App\Services\Http;

use App\Services\AbstractService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Guzzle\HandlerStackFactory;
use Hyperf\Utils\Arr;

class HttpService extends AbstractService
{
    /**
     * @Inject
     * @var GuzzleService
     */
    protected $guzzle;

    /**
     * 发放奖励到交易所
     * @param array $attr
     * @return bool
     */
    public function reward(array $attr = []): bool
    {
        $response = $this->guzzle->create()
            ->post(config('mining.host_exchange') . "/api/position/user-reward", [
                'form_params' => $attr
            ]);
        if ($response->getStatusCode() == 200) {
            $data = json_decode($response->getBody()->getContents(), true);
            $code = Arr::get($data, 'code', 0);
            if ($code == 200) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function info(array $attr = []): array
    {
        $response = $this->guzzle->create()
            ->post(config('mining.host_exchange') . "/api/position/member-info", [
                'form_params' => $attr
            ]);
        if ($response->getStatusCode() == 200) {
            $data = json_decode($response->getBody()->getContents(), true);
            $code = Arr::get($data, 'code', 0);
            if ($code == 200) {
                return $data;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }
}
