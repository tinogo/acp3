<?php
namespace ACP3\Core;

use Doctrine\Common\Cache\CacheProvider;

/**
 * Class Cache
 * @package ACP3\Core
 */
class Cache
{
    /**
     * @var string
     */
    protected $namespace = '';
    /**
     * @var CacheProvider
     */
    protected $driver;

    public function __construct($namespace)
    {
        $this->namespace = $namespace;

        $driverName = defined('CONFIG_CACHE_DRIVER') ? CONFIG_CACHE_DRIVER : 'Array';

        $driverPath = "\\Doctrine\\Common\\Cache\\" . $driverName . 'Cache';
        if (class_exists($driverPath)) {
            if ($driverName === 'PhpFile') {
                $cacheDir = UPLOADS_DIR . 'cache/sql/';
                $this->driver = new $driverPath($cacheDir);
            } else {
                $this->driver = new $driverPath();
            }

            $this->driver->setNamespace($namespace);
        } else {
            throw new \InvalidArgumentException(sprintf('Could not find the requested cache driver "%s"!', $driverPath));
        }
    }

    /**
     * @param $id
     * @return bool|mixed|string
     */
    public function fetch($id)
    {
        return $this->driver->fetch($id);
    }

    /**
     * @param $id
     * @return bool
     */
    public function contains($id)
    {
        return $this->driver->contains($id);
    }

    /**
     * @param $id
     * @param $data
     * @param int $lifetime
     * @return bool
     */
    public function save($id, $data, $lifetime = 0)
    {
        return $this->driver->save($id, $data, $lifetime);
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->driver->delete($id);
    }

    /**
     * @return CacheProvider
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param string $dir
     * @param string $cacheId
     * @return bool
     */
    public static function purge($dir, $cacheId = '')
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = "$dir/$file";

            if (is_dir($path) ) {
                static::purge($path, $cacheId);
                if (empty($cacheId)) {
                    @rmdir($path);
                }
            } else {
                if (!empty($cacheId) && strpos($file, $cacheId) === false) {
                    continue;
                }

                @unlink($path);
            }
        }

        if (!empty($cacheId)) {
            return true;
        }

        return true;
    }
} 