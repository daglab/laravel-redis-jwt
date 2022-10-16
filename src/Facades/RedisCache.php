<?php

namespace Daglab\RedisJWT\Facades;

use Illuminate\Support\Facades\Facade;
use Daglab\RedisJWT\Contracts\RedisCacheContract;

/**
 * Class RedisCache.
 */
class RedisCache extends Facade
{
    /**
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return RedisCacheContract::class;
    }
}
