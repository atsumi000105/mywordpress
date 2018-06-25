<?php
namespace Doctrine\Common\Cache;
abstract class CacheProvider implements Cache, FlushableCache, ClearableCache, MultiGetCache, MultiPutCache
{
    const DOCTRINE_NAMESPACE_CACHEKEY = 'DoctrineNamespaceCacheKey[%s]';
    private $namespace = '';
    private $namespaceVersion;
    public function setNamespace($namespace)
    {
        $this->namespace        = (string) $namespace;
        $this->namespaceVersion = null;
    }
    public function getNamespace()
    {
        return $this->namespace;
    }
    public function fetch($id)
    {
        return $this->doFetch($this->getNamespacedId($id));
    }
    public function fetchMultiple(array $keys)
    {
        if (empty($keys)) {
            return array();
        }
        $namespacedKeys = array_combine($keys, array_map(array($this, 'getNamespacedId'), $keys));
        $items          = $this->doFetchMultiple($namespacedKeys);
        $foundItems     = array();
        foreach ($namespacedKeys as $requestedKey => $namespacedKey) {
            if (isset($items[$namespacedKey]) || array_key_exists($namespacedKey, $items)) {
                $foundItems[$requestedKey] = $items[$namespacedKey];
            }
        }
        return $foundItems;
    }
    public function saveMultiple(array $keysAndValues, $lifetime = 0)
    {
        $namespacedKeysAndValues = array();
        foreach ($keysAndValues as $key => $value) {
            $namespacedKeysAndValues[$this->getNamespacedId($key)] = $value;
        }
        return $this->doSaveMultiple($namespacedKeysAndValues, $lifetime);
    }
    public function contains($id)
    {
        return $this->doContains($this->getNamespacedId($id));
    }
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->doSave($this->getNamespacedId($id), $data, $lifeTime);
    }
    public function delete($id)
    {
        return $this->doDelete($this->getNamespacedId($id));
    }
    public function getStats()
    {
        return $this->doGetStats();
    }
    public function flushAll()
    {
        return $this->doFlush();
    }
    public function deleteAll()
    {
        $namespaceCacheKey = $this->getNamespaceCacheKey();
        $namespaceVersion  = $this->getNamespaceVersion() + 1;
        if ($this->doSave($namespaceCacheKey, $namespaceVersion)) {
            $this->namespaceVersion = $namespaceVersion;
            return true;
        }
        return false;
    }
    private function getNamespacedId($id)
    {
        $namespaceVersion  = $this->getNamespaceVersion();
        return sprintf('%s[%s][%s]', $this->namespace, $id, $namespaceVersion);
    }
    private function getNamespaceCacheKey()
    {
        return sprintf(self::DOCTRINE_NAMESPACE_CACHEKEY, $this->namespace);
    }
    private function getNamespaceVersion()
    {
        if (null !== $this->namespaceVersion) {
            return $this->namespaceVersion;
        }
        $namespaceCacheKey = $this->getNamespaceCacheKey();
        $this->namespaceVersion = $this->doFetch($namespaceCacheKey) ?: 1;
        return $this->namespaceVersion;
    }
    protected function doFetchMultiple(array $keys)
    {
        $returnValues = array();
        foreach ($keys as $key) {
            if (false !== ($item = $this->doFetch($key)) || $this->doContains($key)) {
                $returnValues[$key] = $item;
            }
        }
        return $returnValues;
    }
    abstract protected function doFetch($id);
    abstract protected function doContains($id);
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0)
    {
        $success = true;
        foreach ($keysAndValues as $key => $value) {
            if (!$this->doSave($key, $value, $lifetime)) {
                $success = false;
            }
        }
        return $success;
    }
    abstract protected function doSave($id, $data, $lifeTime = 0);
    abstract protected function doDelete($id);
    abstract protected function doFlush();
    abstract protected function doGetStats();
}
