<?php
namespace Doctrine\Common\Cache;
class FilesystemCache extends FileCache
{
    const EXTENSION = '.doctrinecache.data';
    public function __construct($directory, $extension = self::EXTENSION, $umask = 0002)
    {
        parent::__construct($directory, $extension, $umask);
    }
    protected function doFetch($id)
    {
        $data     = '';
        $lifetime = -1;
        $filename = $this->getFilename($id);
        if ( ! is_file($filename)) {
            return false;
        }
        $resource = fopen($filename, "r");
        if (false !== ($line = fgets($resource))) {
            $lifetime = (int) $line;
        }
        if ($lifetime !== 0 && $lifetime < time()) {
            fclose($resource);
            return false;
        }
        while (false !== ($line = fgets($resource))) {
            $data .= $line;
        }
        fclose($resource);
        return unserialize($data);
    }
    protected function doContains($id)
    {
        $lifetime = -1;
        $filename = $this->getFilename($id);
        if ( ! is_file($filename)) {
            return false;
        }
        $resource = fopen($filename, "r");
        if (false !== ($line = fgets($resource))) {
            $lifetime = (int) $line;
        }
        fclose($resource);
        return $lifetime === 0 || $lifetime > time();
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 0) {
            $lifeTime = time() + $lifeTime;
        }
        $data      = serialize($data);
        $filename  = $this->getFilename($id);
        return $this->writeFile($filename, $lifeTime . PHP_EOL . $data);
    }
}
