<?php
namespace Doctrine\Common\Cache;
use Riak\Bucket;
use Riak\Connection;
use Riak\Input;
use Riak\Exception;
use Riak\Object;
class RiakCache extends CacheProvider
{
    const EXPIRES_HEADER = 'X-Riak-Meta-Expires';
    private $bucket;
    public function __construct(Bucket $bucket)
    {
        $this->bucket = $bucket;
    }
    protected function doFetch($id)
    {
        try {
            $response = $this->bucket->get($id);
            if ( ! $response->hasObject()) {
                return false;
            }
            $object = ($response->hasSiblings())
                ? $this->resolveConflict($id, $response->getVClock(), $response->getObjectList())
                : $response->getFirstObject();
            if ($this->isExpired($object)) {
                $this->bucket->delete($object);
                return false;
            }
            return unserialize($object->getContent());
        } catch (Exception\RiakException $e) {
        }
        return false;
    }
    protected function doContains($id)
    {
        try {
            $input = new Input\GetInput();
            $input->setReturnHead(true);
            $response = $this->bucket->get($id, $input);
            if ( ! $response->hasObject()) {
                return false;
            }
            $object = $response->getFirstObject();
            if ($this->isExpired($object)) {
                $this->bucket->delete($object);
                return false;
            }
            return true;
        } catch (Exception\RiakException $e) {
        }
        return false;
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        try {
            $object = new Object($id);
            $object->setContent(serialize($data));
            if ($lifeTime > 0) {
                $object->addMetadata(self::EXPIRES_HEADER, (string) (time() + $lifeTime));
            }
            $this->bucket->put($object);
            return true;
        } catch (Exception\RiakException $e) {
        }
        return false;
    }
    protected function doDelete($id)
    {
        try {
            $this->bucket->delete($id);
            return true;
        } catch (Exception\BadArgumentsException $e) {
        } catch (Exception\RiakException $e) {
        }
        return false;
    }
    protected function doFlush()
    {
        try {
            $keyList = $this->bucket->getKeyList();
            foreach ($keyList as $key) {
                $this->bucket->delete($key);
            }
            return true;
        } catch (Exception\RiakException $e) {
        }
        return false;
    }
    protected function doGetStats()
    {
        return null;
    }
    private function isExpired(Object $object)
    {
        $metadataMap = $object->getMetadataMap();
        return isset($metadataMap[self::EXPIRES_HEADER])
            && $metadataMap[self::EXPIRES_HEADER] < time();
    }
    protected function resolveConflict($id, $vClock, array $objectList)
    {
        $winner = $objectList[count($objectList)];
        $putInput = new Input\PutInput();
        $putInput->setVClock($vClock);
        $mergedObject = new Object($id);
        $mergedObject->setContent($winner->getContent());
        $this->bucket->put($mergedObject, $putInput);
        return $mergedObject;
    }
}
