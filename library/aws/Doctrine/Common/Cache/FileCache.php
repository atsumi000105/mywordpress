<?php
namespace Doctrine\Common\Cache;
abstract class FileCache extends CacheProvider
{
    protected $directory;
    private $extension;
    private $umask;
    private $directoryStringLength;
    private $extensionStringLength;
    private $isRunningOnWindows;
    public function __construct($directory, $extension = '', $umask = 0002)
    {
        if ( ! is_int($umask)) {
            throw new \InvalidArgumentException(sprintf(
                'The umask parameter is required to be integer, was: %s',
                gettype($umask)
            ));
        }
        $this->umask = $umask;
        if ( ! $this->createPathIfNeeded($directory)) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" does not exist and could not be created.',
                $directory
            ));
        }
        if ( ! is_writable($directory)) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" is not writable.',
                $directory
            ));
        }
        $this->directory = realpath($directory);
        $this->extension = (string) $extension;
        $this->directoryStringLength = strlen($this->directory);
        $this->extensionStringLength = strlen($this->extension);
        $this->isRunningOnWindows    = defined('PHP_WINDOWS_VERSION_BUILD');
    }
    public function getDirectory()
    {
        return $this->directory;
    }
    public function getExtension()
    {
        return $this->extension;
    }
    protected function getFilename($id)
    {
        $hash = hash('sha256', $id);
        if (
            '' === $id
            || ((strlen($id) * 2 + $this->extensionStringLength) > 255)
            || ($this->isRunningOnWindows && ($this->directoryStringLength + 4 + strlen($id) * 2 + $this->extensionStringLength) > 258)
        ) {
            $filename = '_' . $hash;
        } else {
            $filename = bin2hex($id);
        }
        return $this->directory
            . DIRECTORY_SEPARATOR
            . substr($hash, 0, 2)
            . DIRECTORY_SEPARATOR
            . $filename
            . $this->extension;
    }
    protected function doDelete($id)
    {
        $filename = $this->getFilename($id);
        return @unlink($filename) || ! file_exists($filename);
    }
    protected function doFlush()
    {
        foreach ($this->getIterator() as $name => $file) {
            if ($file->isDir()) {
                @rmdir($name);
            } elseif ($this->isFilenameEndingWithExtension($name)) {
                @unlink($name);
            }
        }
        return true;
    }
    protected function doGetStats()
    {
        $usage = 0;
        foreach ($this->getIterator() as $name => $file) {
            if (! $file->isDir() && $this->isFilenameEndingWithExtension($name)) {
                $usage += $file->getSize();
            }
        }
        $free = disk_free_space($this->directory);
        return array(
            Cache::STATS_HITS               => null,
            Cache::STATS_MISSES             => null,
            Cache::STATS_UPTIME             => null,
            Cache::STATS_MEMORY_USAGE       => $usage,
            Cache::STATS_MEMORY_AVAILABLE   => $free,
        );
    }
    private function createPathIfNeeded($path)
    {
        if ( ! is_dir($path)) {
            if (false === @mkdir($path, 0777 & (~$this->umask), true) && !is_dir($path)) {
                return false;
            }
        }
        return true;
    }
    protected function writeFile($filename, $content)
    {
        $filepath = pathinfo($filename, PATHINFO_DIRNAME);
        if ( ! $this->createPathIfNeeded($filepath)) {
            return false;
        }
        if ( ! is_writable($filepath)) {
            return false;
        }
        $tmpFile = tempnam($filepath, 'swap');
        @chmod($tmpFile, 0666 & (~$this->umask));
        if (file_put_contents($tmpFile, $content) !== false) {
            if (@rename($tmpFile, $filename)) {
                return true;
            }
            @unlink($tmpFile);
        }
        return false;
    }
    private function getIterator()
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
    }
    private function isFilenameEndingWithExtension($name)
    {
        return '' === $this->extension
            || strrpos($name, $this->extension) === (strlen($name) - $this->extensionStringLength);
    }
}
