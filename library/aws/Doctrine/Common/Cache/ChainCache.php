<?php
namespace Doctrine\Common\Cache;
class ChainCache extends CacheProvider
{
    private $cacheProviders = array();
    public function __construct($cacheProviders = array())
    {
        $this->cacheProviders = $cacheProviders;
    }
    public function setNamespace($namespace)
    {
        parent::setNamespace($namespace);
        foreach ($this->cacheProviders as $cacheProvider) {
            $cacheProvider->setNamespace($namespace);
        }
    }
    protected function doFetch($id)
    {
        foreach ($this->cacheProviders as $key => $cacheProvider) {
            if ($cacheProvider->doContains($id)) {
                $value = $cacheProvider->doFetch($id);
                for ($subKey = $key - 1 ; $subKey >= 0 ; $subKey--) {
                    $this->cacheProviders[$subKey]->doSave($id, $value);
                }
                return $value;
            }
        }
        return false;
    }
    protected function doContains($id)
    {
        foreach ($this->cacheProviders as $cacheProvider) {
            if ($cacheProvider->doContains($id)) {
                return true;
            }
        }
        return false;
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        $stored = true;
        foreach ($this->cacheProviders as $cacheProvider) {
            $stored = $cacheProvider->doSave($id, $data, $lifeTime) && $stored;
        }
        return $stored;
    }
    protected function doDelete($id)
    {
        $deleted = true;
        foreach ($this->cacheProviders as $cacheProvider) {
            $deleted = $cacheProvider->doDelete($id) && $deleted;
        }
        return $deleted;
    }
    protected function doFlush()
    {
        $flushed = true;
        foreach ($this->cacheProviders as $cacheProvider) {
            $flushed = $cacheProvider->doFlush() && $flushed;
        }
        return $flushed;
    }
    protected function doGetStats()
    {
        $stats = array();
        foreach ($this->cacheProviders as $cacheProvider) {
            $stats[] = $cacheProvider->doGetStats();
        }
        return $stats;
    }
}
