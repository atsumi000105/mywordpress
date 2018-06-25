<?php
namespace Doctrine\Common\Cache;
use Predis\ClientInterface;
class PredisCache extends CacheProvider
{
    private $client;
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }
    protected function doFetch($id)
    {
        $result = $this->client->get($id);
        if (null === $result) {
            return false;
        }
        return unserialize($result);
    }
    protected function doFetchMultiple(array $keys)
    {
        $fetchedItems = call_user_func_array(array($this->client, 'mget'), $keys);
        return array_map('unserialize', array_filter(array_combine($keys, $fetchedItems)));
    }
    protected function doSaveMultiple(array $keysAndValues, $lifetime = 0)
    {
        if ($lifetime) {
            $success = true;
            foreach ($keysAndValues as $key => $value) {
                $response = $this->client->setex($key, $lifetime, serialize($value));
                if ((string) $response != 'OK') {
                    $success = false;
                }
            }
            return $success;
        }
        $response = $this->client->mset(array_map(function ($value) {
            return serialize($value);
        }, $keysAndValues));
        return (string) $response == 'OK';
    }
    protected function doContains($id)
    {
        return $this->client->exists($id);
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        $data = serialize($data);
        if ($lifeTime > 0) {
            $response = $this->client->setex($id, $lifeTime, $data);
        } else {
            $response = $this->client->set($id, $data);
        }
        return $response === true || $response == 'OK';
    }
    protected function doDelete($id)
    {
        return $this->client->del($id) >= 0;
    }
    protected function doFlush()
    {
        $response = $this->client->flushdb();
        return $response === true || $response == 'OK';
    }
    protected function doGetStats()
    {
        $info = $this->client->info();
        return array(
            Cache::STATS_HITS              => $info['Stats']['keyspace_hits'],
            Cache::STATS_MISSES            => $info['Stats']['keyspace_misses'],
            Cache::STATS_UPTIME            => $info['Server']['uptime_in_seconds'],
            Cache::STATS_MEMORY_USAGE      => $info['Memory']['used_memory'],
            Cache::STATS_MEMORY_AVAILABLE  => false
        );
    }
}
