<?php

namespace Tymon\JWTAuth\Providers\Storage;

use Illuminate\Cache\CacheManager;
use Tymon\JWTAuth\Contracts\Providers\Storage;

class Illuminate implements Storage
{
    /**
     * @var \Illuminate\Cache\CacheManager
     */
    protected $cache;

    /**
     * @var string
     */
    protected $tag = 'tymon.jwt';

    /**
     * @param \Illuminate\Cache\CacheManager  $cache
     */
    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Add a new item into storage
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $minutes
     *
     * @return void
     */
    public function add($key, $value, $minutes)
    {
        $this->cache()->put($key, $value, $minutes);
    }

    /**
     * Get an item from storage
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->cache()->get($key);
    }

    /**
     * Remove an item from storage
     *
     * @param  string  $key
     *
     * @return boolean
     */
    public function destroy($key)
    {
        return $this->cache()->forget($key);
    }

    /**
     * Remove all items associated with the tag
     *
     * @return void
     */
    public function flush()
    {
        $this->cache()->flush();
    }

    /**
     * Return the cache instance with tags attached
     *
     * @return \Illuminate\Cache\CacheManager
     */
    protected function cache()
    {
        if (! method_exists($this->cache, 'tags')) {
            return $this->cache;
        }

        return $this->cache->tags($this->tag);
    }
}
