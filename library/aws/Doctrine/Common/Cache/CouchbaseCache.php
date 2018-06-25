<?php
namespace Doctrine\Common\Cache;
use \Couchbase;
class CouchbaseCache extends CacheProvider
{
    private $couchbase;
    public function setCouchbase(Couchbase $couchbase)
    {
        $this->couchbase = $couchbase;
    }
    public function getCouchbase()
    {
        return $this->couchbase;
    }
    protected function doFetch($id)
    {
        return $this->couchbase->get($id) ?: false;
    }
    protected function doContains($id)
    {
        return (null !== $this->couchbase->get($id));
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 30 * 24 * 3600) {
            $lifeTime = time() + $lifeTime;
        }
        return $this->couchbase->set($id, $data, (int) $lifeTime);
    }
    protected function doDelete($id)
    {
        return $this->couchbase->delete($id);
    }
    protected function doFlush()
    {
        return $this->couchbase->flush();
    }
    protected function doGetStats()
    {
        $stats   = $this->couchbase->getStats();
        $servers = $this->couchbase->getServers();
        $server  = explode(":", $servers[0]);
        $key     = $server[0] . ":" . "11210";
        $stats   = $stats[$key];
        return array(
            Cache::STATS_HITS   => $stats['get_hits'],
            Cache::STATS_MISSES => $stats['get_misses'],
            Cache::STATS_UPTIME => $stats['uptime'],
            Cache::STATS_MEMORY_USAGE     => $stats['bytes'],
            Cache::STATS_MEMORY_AVAILABLE => $stats['limit_maxbytes'],
        );
    }
}
