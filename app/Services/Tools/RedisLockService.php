<?php

namespace App\Services\Tools;

use App\Services\AbstractService;
use App\Services\Base\BaseRedisService;

class RedisLockService extends AbstractService
{
    use BaseRedisService;

    protected $key = '';
    protected $timeout = 0;

    protected function redisLock(string $key, int $timeout): bool
    {
        $this->key = $key;
        $rs = $this->redis()->set($key, 1, ['nx', $timeout]);
        return $rs;
    }

    public static function lock(string $key, int $timeout)
    {
        $rss = make(RedisLockService::class);
        $rs = $rss->redisLock($key, $timeout);
        return $rss;
    }


    public function wait(int $timeout = 5)
    {
        $use_time = '0';
        while (true) {
            usleep(500 * 1000);
            if ($this->redisLock($this->key, $this->timeout)) {
                break;
            }
            $use_time = bcadd($use_time, '0.5', 1);
            if (bccomp($use_time, (string)$timeout) >= 0) {
                throw new \Exception('lock timeout!');
            }
        }
    }

    public function delete()
    {
        $this->redis()->del($this->key);
    }
}
