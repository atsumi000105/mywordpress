<?php
namespace Doctrine\Common\Cache;
class VoidCache extends CacheProvider
{
    protected function doFetch($id)
    {
        return false;
    }
    protected function doContains($id)
    {
        return false;
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        return true;
    }
    protected function doDelete($id)
    {
        return true;
    }
    protected function doFlush()
    {
        return true;
    }
    protected function doGetStats()
    {
        return;
    }
}
