<?php
namespace Doctrine\Common\Cache;
class ZendDataCache extends CacheProvider
{
    protected function doFetch($id)
    {
        return zend_shm_cache_fetch($id);
    }
    protected function doContains($id)
    {
        return (false !== zend_shm_cache_fetch($id));
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return zend_shm_cache_store($id, $data, $lifeTime);
    }
    protected function doDelete($id)
    {
        return zend_shm_cache_delete($id);
    }
    protected function doFlush()
    {
        $namespace = $this->getNamespace();
        if (empty($namespace)) {
            return zend_shm_cache_clear();
        }
        return zend_shm_cache_clear($namespace);
    }
    protected function doGetStats()
    {
        return null;
    }
}
