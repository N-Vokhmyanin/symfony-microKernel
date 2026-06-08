<?php

namespace Core\Services\Cache;

class RedisCacheFactory
{

    public static function createRedisCache(string $configName): RedisCacheService
    {
        $client = \App\Redis::getDb($configName);
        if ($configName === 'sessions') {
            $client = \App\Sessions::getRedis();
        }

        return new RedisCacheService($client, $configName);
    }
}