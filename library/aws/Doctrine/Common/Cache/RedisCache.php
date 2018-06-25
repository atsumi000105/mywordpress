<?php
namespace Doctrine\Common\Cache;
use Redis;
class RedisCache extends CacheProvider
{
    private $redis;
    public function setRedis(Redis $redis)
    {
        $redis->setOption(Redis::OPT_SERIALIZER, $this->getSerializerValue());
        $this->redis = $redis;
    }
    public function getRedis()
    {
        return $this->redis;
    }
    protected function doFetch($id)
    {
        return $this->redis->get($id);
    }
    protected function doFetchMultiple(array $keys)
    {
        $fetchedItems = array_combine($keys, $this->redis->mget($keys));
        $foundItems   = array();
        foreach ($fetchedItems as $key => $value) {
            if (false !== $value || $this->redis->exists($key)) {
                $foundItems[$key] = $value;
            }
        }
        return $foundItems;
    }
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0)
    {
        if ($lifetime) {
            $success = true;
            foreach ($keysAndValues as $key => $value) {
                if (!$this->redis->setex($key, $lifetime, $value)) {
                    $success = false;
                }
            }
            return $success;
        }
        return (bool) $this->redis->mset($keysAndValues);
    }
    protected function doContains($id)
    {
        return $this->redis->exists($id);
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 0) {
            return $this->redis->setex($id, $lifeTime, $data);
        }
        return $this->redis->set($id, $data);
    }
    protected function doDelete($id)
    {
        return $this->redis->delete($id) >= 0;
    }
    protected function doFlush()
    {
        return $this->redis->flushDB();
    }
    protected function doGetStats()
    {
        $info = $this->redis->info();
        return array(
            Cache::STATS_HITS   => $info['keyspace_hits'],
            Cache::STATS_MISSES => $info['keyspace_misses'],
            Cache::STATS_UPTIME => $info['uptime_in_seconds'],
            Cache::STATS_MEMORY_USAGE      => $info['used_memory'],
            Cache::STATS_MEMORY_AVAILABLE  => false
        );
    }
    protected function getSerializerValue()
    {
        if (defined('HHVM_VERSION')) {
            return Redis::SERIALIZER_PHP;
        }
        return defined('Redis::SERIALIZER_IGBINARY') ? Redis::SERIALIZER_IGBINARY : Redis::SERIALIZER_PHP;
    }
}
