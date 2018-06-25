<?php
namespace Doctrine\Common\Cache;
class ArrayCache extends CacheProvider
{
    private $data = [];
    private $hitsCount = 0;
    private $missesCount = 0;
    private $upTime;
    public function __construct()
    {
        $this->upTime = time();
    }
    protected function doFetch($id)
    {
        if (! $this->doContains($id)) {
            $this->missesCount += 1;
            return false;
        }
        $this->hitsCount += 1;
        return $this->data[$id][0];
    }
    protected function doContains($id)
    {
        if (! isset($this->data[$id])) {
            return false;
        }
        $expiration = $this->data[$id][1];
        if ($expiration && $expiration < time()) {
            $this->doDelete($id);
            return false;
        }
        return true;
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        $this->data[$id] = [$data, $lifeTime ? time() + $lifeTime : false];
        return true;
    }
    protected function doDelete($id)
    {
        unset($this->data[$id]);
        return true;
    }
    protected function doFlush()
    {
        $this->data = [];
        return true;
    }
    protected function doGetStats()
    {
        return [
            Cache::STATS_HITS             => $this->hitsCount,
            Cache::STATS_MISSES           => $this->missesCount,
            Cache::STATS_UPTIME           => $this->upTime,
            Cache::STATS_MEMORY_USAGE     => null,
            Cache::STATS_MEMORY_AVAILABLE => null,
        ];
    }
}
