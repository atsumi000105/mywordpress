<?php
namespace Doctrine\Common\Cache;
use \Memcached;
class MemcachedCache extends CacheProvider
{
    private $memcached;
    public function setMemcached(Memcached $memcached)
    {
        $this->memcached = $memcached;
    }
    public function getMemcached()
    {
        return $this->memcached;
    }
    protected function doFetch($id)
    {
        return $this->memcached->get($id);
    }
    protected function doFetchMultiple(array $keys)
    {
        return $this->memcached->getMulti($keys);
    }
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0)
    {
        if ($lifetime > 30 * 24 * 3600) {
            $lifetime = time() + $lifetime;
        }
        return $this->memcached->setMulti($keysAndValues, null, $lifetime);
    }
    protected function doContains($id)
    {
        return false !== $this->memcached->get($id)
            || $this->memcached->getResultCode() !== Memcached::RES_NOTFOUND;
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 30 * 24 * 3600) {
            $lifeTime = time() + $lifeTime;
        }
        return $this->memcached->set($id, $data, (int) $lifeTime);
    }
    protected function doDelete($id)
    {
        return $this->memcached->delete($id)
            || $this->memcached->getResultCode() === Memcached::RES_NOTFOUND;
    }
    protected function doFlush()
    {
        return $this->memcached->flush();
    }
    protected function doGetStats()
    {
        $stats   = $this->memcached->getStats();
        $servers = $this->memcached->getServerList();
        $key     = $servers[0]['host'] . ':' . $servers[0]['port'];
        $stats   = $stats[$key];
        return array(
            Cache::STATS_HITS   => $stats['get_hits'],
            Cache::STATS_MISSES => $stats['get_misses'],
            Cache::STATS_UPTIME => $stats['uptime'],
            Cache::STATS_MEMORY_USAGE     => $stats['bytes'],
            Cache::STATS_MEMORY_AVAILABLE => $stats['limit_maxbytes'],
        );
    }
}
