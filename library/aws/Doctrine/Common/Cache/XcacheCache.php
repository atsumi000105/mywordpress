<?php
namespace Doctrine\Common\Cache;
class XcacheCache extends CacheProvider
{
    protected function doFetch($id)
    {
        return $this->doContains($id) ? unserialize(xcache_get($id)) : false;
    }
    protected function doContains($id)
    {
        return xcache_isset($id);
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return xcache_set($id, serialize($data), (int) $lifeTime);
    }
    protected function doDelete($id)
    {
        return xcache_unset($id);
    }
    protected function doFlush()
    {
        $this->checkAuthorization();
        xcache_clear_cache(XC_TYPE_VAR);
        return true;
    }
    protected function checkAuthorization()
    {
        if (ini_get('xcache.admin.enable_auth')) {
            throw new \BadMethodCallException(
                'To use all features of \Doctrine\Common\Cache\XcacheCache, '
                . 'you must set "xcache.admin.enable_auth" to "Off" in your php.ini.'
            );
        }
    }
    protected function doGetStats()
    {
        $this->checkAuthorization();
        $info = xcache_info(XC_TYPE_VAR, 0);
        return array(
            Cache::STATS_HITS   => $info['hits'],
            Cache::STATS_MISSES => $info['misses'],
            Cache::STATS_UPTIME => null,
            Cache::STATS_MEMORY_USAGE      => $info['size'],
            Cache::STATS_MEMORY_AVAILABLE  => $info['avail'],
        );
    }
}
