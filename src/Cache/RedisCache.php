<?php

namespace Daglab\RedisJWT\Cache;

use Illuminate\Support\Facades\Cache;
use Daglab\RedisJWT\Contracts\RedisCacheContract;

class RedisCache implements RedisCacheContract
{
    /** @var mixed */
    protected $data;

    /** @var int */
    private $time;

    /** @var string */
    protected $key;

    /**
     * @param string $key
     *
     * @return RedisCacheContract
     */
    public function key(string $key): RedisCacheContract
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @param $data
     *
     * @return RedisCacheContract
     */
    public function data($data): RedisCacheContract
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCache()
    {
        return Cache::get($this->key);
    }

    /**
     * @return bool
     */
    public function removeCache(): bool
    {
        return Cache::forget($this->key);
    }

    /**
     * @return bool|mixed
     */
    public function refreshCache()
    {
        if (!$this->getCache()) {
            return false;
        }

        $this->key($this->key)->removeCache();

        return $this->key($this->key)->data($this->data)->cache();
    }

    /**
     * @return mixed
     */
    public function cache()
    {
        $this->setTime();

        return Cache::remember($this->key, $this->time, function () {
            return $this->data;
        });
    }

    /**
     * @return $this
     */
    private function setTime(): RedisCacheContract
    {
        $this->time = (config('jwt-redis.redis_ttl_jwt') ? config('jwt.ttl') : config('jwt-redis.redis_ttl')) * 60;

        return $this;
    }
}
