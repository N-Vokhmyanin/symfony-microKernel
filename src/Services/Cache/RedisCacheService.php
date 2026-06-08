<?php

namespace Core\Services\Cache;

class RedisCacheService extends AbstractRedisCache
{
    public function __construct($redis, string $configName)
    {
        $this->cacheProvider = $redis;
        $this->configName = $configName;
    }
}