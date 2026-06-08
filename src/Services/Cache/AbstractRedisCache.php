<?php

namespace Core\Services\Cache;

use Symfony\Contracts\Cache\CacheInterface;

abstract class AbstractRedisCache implements CacheInterface
{
    protected $cacheProvider;
    protected string $configName;

    public function get(string $key, ?callable $callback = null, ?float $beta = null, ?array &$metadata = null)
    {
        return $this->cacheProvider->get($key);
    }

    public function delete(string $key): bool
    {
        return $this->cacheProvider->delete($key);
    }
}