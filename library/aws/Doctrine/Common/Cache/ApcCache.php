<?php
namespace Doctrine\Common\Cache;
class ApcCache extends CacheProvider
{
    protected function doFetch($id)
    {
        return apc_fetch($id);
    }
    protected function doContains($id)
    {
        return apc_exists($id);
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return apc_store($id, $data, $lifeTime);
    }
    protected function doDelete($id)
    {
        return apc_delete($id) || ! apc_exists($id);
    }
    protected function doFlush()
    {
        return apc_clear_cache() && apc_clear_cache('user');
    }
    protected function doFetchMultiple(array $keys)
    {
        return apc_fetch($keys);
    }
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0)
    {
        $result = apc_store($keysAndValues, null, $lifetime);
        return empty($result);
    }
    protected function doGetStats()
    {
        $info = apc_cache_info('', true);
        $sma  = apc_sma_info();
        if (PHP_VERSION_ID >= 50500) {
            $info['num_hits']   = isset($info['num_hits'])   ? $info['num_hits']   : $info['nhits'];
            $info['num_misses'] = isset($info['num_misses']) ? $info['num_misses'] : $info['nmisses'];
            $info['start_time'] = isset($info['start_time']) ? $info['start_time'] : $info['stime'];
        }
        return array(
            Cache::STATS_HITS             => $info['num_hits'],
            Cache::STATS_MISSES           => $info['num_misses'],
            Cache::STATS_UPTIME           => $info['start_time'],
            Cache::STATS_MEMORY_USAGE     => $info['mem_size'],
            Cache::STATS_MEMORY_AVAILABLE => $sma['avail_mem'],
        );
    }
}
