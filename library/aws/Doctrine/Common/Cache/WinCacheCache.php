<?php
namespace Doctrine\Common\Cache;
class WinCacheCache extends CacheProvider
{
    protected function doFetch($id)
    {
        return wincache_ucache_get($id);
    }
    protected function doContains($id)
    {
        return wincache_ucache_exists($id);
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return wincache_ucache_set($id, $data, $lifeTime);
    }
    protected function doDelete($id)
    {
        return wincache_ucache_delete($id);
    }
    protected function doFlush()
    {
        return wincache_ucache_clear();
    }
    protected function doFetchMultiple(array $keys)
    {
        return wincache_ucache_get($keys);
    }
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0)
    {
        $result = wincache_ucache_set($keysAndValues, null, $lifetime);
        return empty($result);
    }
    protected function doGetStats()
    {
        $info    = wincache_ucache_info();
        $meminfo = wincache_ucache_meminfo();
        return array(
            Cache::STATS_HITS             => $info['total_hit_count'],
            Cache::STATS_MISSES           => $info['total_miss_count'],
            Cache::STATS_UPTIME           => $info['total_cache_uptime'],
            Cache::STATS_MEMORY_USAGE     => $meminfo['memory_total'],
            Cache::STATS_MEMORY_AVAILABLE => $meminfo['memory_free'],
        );
    }
}
