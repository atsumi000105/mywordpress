<?php
namespace Doctrine\Common\Cache;
class ApcuCache extends CacheProvider
{
    protected function doFetch($id)
    {
        return apcu_fetch($id);
    }
    protected function doContains($id)
    {
        return apcu_exists($id);
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return apcu_store($id, $data, $lifeTime);
    }
    protected function doDelete($id)
    {
        return apcu_delete($id) || ! apcu_exists($id);
    }
    protected function doFlush()
    {
        return apcu_clear_cache();
    }
    protected function doFetchMultiple(array $keys)
    {
        return apcu_fetch($keys);
    }
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0)
    {
        $result = apcu_store($keysAndValues, null, $lifetime);
        return empty($result);
    }
    protected function doGetStats()
    {
        $info = apcu_cache_info(true);
        $sma  = apcu_sma_info();
        return array(
            Cache::STATS_HITS             => $info['num_hits'],
            Cache::STATS_MISSES           => $info['num_misses'],
            Cache::STATS_UPTIME           => $info['start_time'],
            Cache::STATS_MEMORY_USAGE     => $info['mem_size'],
            Cache::STATS_MEMORY_AVAILABLE => $sma['avail_mem'],
        );
    }
}
