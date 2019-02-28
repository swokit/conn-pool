<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/2/6 0006
 * Time: 20:35
 */

namespace Swokit\Pool;

use Swokit\Pool\MySQL\YieldDriverPool;
use Toolkit\Pool\PoolInterface;

/**
 * Class PoolManager
 * @package Swokit\Pool
 */
class PoolManager
{
    /**
     * @var self
     */
    private static $_instance;

    /**
     * @var PoolInterface[]
     */
    protected $pools = [];

    /**
     * @var array
     */
    protected $configs = [];

    /**
     * @return PoolManager
     */
    public static function instance(): PoolManager
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function __destruct()
    {
        foreach ($this->pools as $pool) {
            $pool->clear();
        }
    }

    public function init(): void
    {
        foreach ($this->configs as $config) {
            $pool = new YieldDriverPool($config);
            $pool->initPool();

            $this->pools[$pool->getName()] = $pool;
        }
    }

    /**
     * @param string $poolName
     * @return mixed
     */
    public function get(string $poolName)
    {
        if (isset($this->pools[$poolName])) {
            return $this->pools[$poolName]->get();
        }

        return null;
    }

    /**
     * @param string $poolName
     * @param mixed $resource
     */
    public function put(string $poolName, $resource): void
    {
        if (isset($this->pools[$poolName])) {
            $this->pools[$poolName]->put($resource);
        }
    }

    public function clear(string $poolName = null): void
    {
        if ($poolName && isset($this->pools[$poolName])) {
            $this->pools[$poolName]->clear();
        } else {
            foreach ($this->pools as $pool) {
                $pool->clear();
            }
        }
    }

    /**
     * @param string $poolName
     * @return PoolInterface|null
     */
    public function getPool(string $poolName): ?PoolInterface
    {
        return $this->pools[$poolName] ?? null;
    }

    /**
     * @param string $poolName
     * @return bool
     */
    public function hasPool(string $poolName): bool
    {
        return isset($this->pools[$poolName]);
    }
}
