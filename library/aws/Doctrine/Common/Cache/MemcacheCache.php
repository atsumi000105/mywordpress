<?php
namespace Doctrine\Common\Cache;
use \Memcache;
class MemcacheCache extends CacheProvider
{
    private $memcache;
    public function setMemcache(Memcache $memcache)
    {
        $this->memcache = $memcache;
    }
    public function getMemcache()
    {
        return $this->memcache;
    }
    protected function doFetch($id)
    {
        return $this->memcache->get($id);
    }
    protected function doContains($id)
    {
        $flags = null;
        $this->memcache->get($id, $flags);
        return ($flags !== null);
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 30 * 24 * 3600) {
            $lifeTime = time() + $lifeTime;
        }
        return $this->memcache->set($id, $data, 0, (int) $lifeTime);
    }
    protected function doDelete($id)
    {
        return $this->memcache->delete($id) || ! $this->doContains($id);
    }
    protected function doFlush()
    {
        return $this->memcache->flush();
    }
    protected function doGetStats()
    {
        $stats = $this->memcache->getStats();
        return array(
            Cache::STATS_HITS   => $stats['get_hits'],
            Cache::STATS_MISSES => $stats['get_misses'],
            Cache::STATS_UPTIME => $stats['uptime'],
            Cache::STATS_MEMORY_USAGE     => $stats['bytes'],
            Cache::STATS_MEMORY_AVAILABLE => $stats['limit_maxbytes'],
        );
    }
}
