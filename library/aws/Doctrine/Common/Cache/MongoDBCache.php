<?php
namespace Doctrine\Common\Cache;
use MongoBinData;
use MongoCollection;
use MongoCursorException;
use MongoDate;
class MongoDBCache extends CacheProvider
{
    const DATA_FIELD = 'd';
    const EXPIRATION_FIELD = 'e';
    private $collection;
    public function __construct(MongoCollection $collection)
    {
        $this->collection = $collection;
    }
    protected function doFetch($id)
    {
        $document = $this->collection->findOne(array('_id' => $id), array(self::DATA_FIELD, self::EXPIRATION_FIELD));
        if ($document === null) {
            return false;
        }
        if ($this->isExpired($document)) {
            $this->doDelete($id);
            return false;
        }
        return unserialize($document[self::DATA_FIELD]->bin);
    }
    protected function doContains($id)
    {
        $document = $this->collection->findOne(array('_id' => $id), array(self::EXPIRATION_FIELD));
        if ($document === null) {
            return false;
        }
        if ($this->isExpired($document)) {
            $this->doDelete($id);
            return false;
        }
        return true;
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        try {
            $result = $this->collection->update(
                array('_id' => $id),
                array('$set' => array(
                    self::EXPIRATION_FIELD => ($lifeTime > 0 ? new MongoDate(time() + $lifeTime) : null),
                    self::DATA_FIELD => new MongoBinData(serialize($data), MongoBinData::BYTE_ARRAY),
                )),
                array('upsert' => true, 'multiple' => false)
            );
        } catch (MongoCursorException $e) {
            return false;
        }
        return isset($result['ok']) ? $result['ok'] == 1 : true;
    }
    protected function doDelete($id)
    {
        $result = $this->collection->remove(array('_id' => $id));
        return isset($result['ok']) ? $result['ok'] == 1 : true;
    }
    protected function doFlush()
    {
        $result = $this->collection->remove();
        return isset($result['ok']) ? $result['ok'] == 1 : true;
    }
    protected function doGetStats()
    {
        $serverStatus = $this->collection->db->command(array(
            'serverStatus' => 1,
            'locks' => 0,
            'metrics' => 0,
            'recordStats' => 0,
            'repl' => 0,
        ));
        $collStats = $this->collection->db->command(array('collStats' => 1));
        return array(
            Cache::STATS_HITS => null,
            Cache::STATS_MISSES => null,
            Cache::STATS_UPTIME => (isset($serverStatus['uptime']) ? (int) $serverStatus['uptime'] : null),
            Cache::STATS_MEMORY_USAGE => (isset($collStats['size']) ? (int) $collStats['size'] : null),
            Cache::STATS_MEMORY_AVAILABLE  => null,
        );
    }
    private function isExpired(array $document)
    {
        return isset($document[self::EXPIRATION_FIELD]) &&
            $document[self::EXPIRATION_FIELD] instanceof MongoDate &&
            $document[self::EXPIRATION_FIELD]->sec < time();
    }
}
