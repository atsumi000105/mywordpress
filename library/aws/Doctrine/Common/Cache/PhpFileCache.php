<?php
namespace Doctrine\Common\Cache;
class PhpFileCache extends FileCache
{
    const EXTENSION = '.doctrinecache.php';
    public function __construct($directory, $extension = self::EXTENSION, $umask = 0002)
    {
        parent::__construct($directory, $extension, $umask);
    }
    protected function doFetch($id)
    {
        $value = $this->includeFileForId($id);
        if (! $value) {
            return false;
        }
        if ($value['lifetime'] !== 0 && $value['lifetime'] < time()) {
            return false;
        }
        return $value['data'];
    }
    protected function doContains($id)
    {
        $value = $this->includeFileForId($id);
        if (! $value) {
            return false;
        }
        return $value['lifetime'] === 0 || $value['lifetime'] > time();
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 0) {
            $lifeTime = time() + $lifeTime;
        }
        if (is_object($data) && ! method_exists($data, '__set_state')) {
            throw new \InvalidArgumentException(
                "Invalid argument given, PhpFileCache only allows objects that implement __set_state() " .
                "and fully support var_export(). You can use the FilesystemCache to save arbitrary object " .
                "graphs using serialize()/deserialize()."
            );
        }
        $filename  = $this->getFilename($id);
        $value = array(
            'lifetime'  => $lifeTime,
            'data'      => $data
        );
        $value  = var_export($value, true);
        $code   = sprintf('<?php return %s;', $value);
        return $this->writeFile($filename, $code);
    }
    private function includeFileForId($id)
    {
        $fileName = $this->getFilename($id);
        $value = @include $fileName;
        if (! isset($value['lifetime'])) {
            return false;
        }
        return $value;
    }
}
