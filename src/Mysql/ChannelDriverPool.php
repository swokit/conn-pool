<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018/3/1
 * Time: 下午5:23
 */

namespace SwoKit\Pool\Mysql;

use SwoKit\Pool\Exception\ChannelException;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\MySQL;
use Toolkit\Pool\AbstractPool;

/**
 * Class ChannelDriverPool
 * @package SwoKit\Pool\Mysql
 */
class ChannelDriverPool extends AbstractPool
{
    /**
     * @var array
     */
    protected $options = [
        'db1' => [
            'host' => 'mysql',
            'port' => 3306,
            'user' => 'root',
            'password' => 'password',
            'database' => 'my_test',
        ],
    ];

    /**
     * @var Channel
     * [
     *  connection0,
     *  connection1,
     *  connection2,
     *  ... ...
     * ]
     */
    private $chan;

    /**
     * @var int
     */
    private $busyCount = 0;

    /**
     * @var array
     */
    private $stats = [
        'total' => 0,
        'free' => 0,
        'busy' => 0,
    ];

    protected function init()
    {
        $this->chan = new Channel($this->maxSize);

        // parent::init();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDbConfig(): array
    {
        $count = \count($this->options);

        if ($count === 1) {
            $config = \array_values($this->options)[0];
        } else {
            $index = \random_int(0, $count - 1);
            $config = \array_values($this->options)[$index];
        }

        return $config;
    }

    /**
     * 预(创建)准备资源
     * @param int $size
     * @return int
     * @throws \Exception
     */
    protected function prepare(int $size): int
    {
        if ($size <= 0) {
            return 0;
        }

        for ($i = 0; $i < $size; $i++) {
            $res = $this->create();
            $this->chan->push($res);
        }

        return $size;
    }

    /**
     * 创建新的资源实例
     * @return mixed
     * @throws \Exception
     */
    public function create()
    {
        $config = $this->getDbConfig();

        $db = new MySQL();
        if (!$db->connect($config)) {
            throw new \RuntimeException('connect to database server failure. info=' . \json_encode($config));
        }

        // add metadata
        $id = $this->genID($db);
        $time = \time();
        $this->metas[$id] = [
            'createAt' => $time,
            'activeAt' => $time,
            'config' => $config, // record for reconnection.
        ];

        return $db;
    }

    /**
     * @return mixed|MySQL
     * @throws \Exception
     */
    public function get()
    {
        // no conn in the chan. create new.
        if ($this->chan->length() === 0) {
            $db = $this->create();
        } else {
            // get from channel
            $db = $this->chan->pop($this->waitTimeout / 1000);

            // check connection
            if (!$this->validate($db)) {
                $db = $this->create();
            }
        }

        $this->busyCount++;

        return $db;
    }

    /**
     * @param mixed $obj
     */
    public function put($obj)
    {
        // update active time
        $resId = $this->genID($obj);
        $this->metas[$resId]['activeAt'] = \time();

        if (!$this->chan->push($obj)) {
            throw new ChannelException('push resource to chan is failure!', -500);
        }

        $this->busyCount--;
    }

    /**
     * 销毁资源实例
     * @param mixed|MySQL $obj
     * @return void
     */
    public function destroy($obj)
    {
        // del metadata
        $id = $this->genID($obj);

        if (isset($this->metas[$id])) {
            unset($this->metas[$id]);
        }

        $obj = null;
    }

    /**
     * 验证资源(eg. db connection)有效性
     * @param mixed|MySQL $obj
     * @return bool
     */
    protected function validate($obj): bool
    {
        $time = \time();
        $resId = $this->genID($obj);

        if (!$meta = $this->getMeta($resId)) {
            $this->destroy($obj);
            return false;
        }

        // check max lifetime
        $lifetime = $this->maxLifetime * 60;
        if ($time - $meta['createAt'] >= $lifetime) {
            $this->destroy($obj);
            return false;
        }

        // if lost connection, reconnection it.
        if (!$obj->connected) {
            return $this->reconnection($obj);
        }

        return true;
    }

    /**
     * reconnection to DB server
     * @param mixed|MySQL $obj
     * @return bool
     */
    public function reconnection($obj): bool
    {
        $resId = $this->genID($obj);
        $info = $this->getMeta($resId);

        if (!$obj->connect($info)) {
            $this->destroy($obj);
            return false;
        }

        return true;
    }

    public function clear()
    {
        $this->busyCount = 0;

        if (Coroutine::getuid() === -1) {
            return;
        }

        // empty
        if ($this->chan->length() === 0) {
            return;
        }

        $timeout = $this->waitTimeout / 1000;
        while ($db = $this->chan->pop($timeout)) {
            $this->destroy($db);
        }

        $this->chan->close();
    }

    /**
     * @return Channel
     */
    public function getChan(): Channel
    {
        return $this->chan;
    }

    /**
     * @return array
     */
    public function getStats(): array
    {
        return $this->chan->stats();
    }

    /**
     * @return int
     */
    public function waitingCount(): int
    {
        return $this->chan->stats()['queue_num'];
    }

    /**
     * @return bool
     */
    public function hasWaiting(): bool
    {
        return $this->waitingCount() > 0;
    }

    /**
     * @return int
     */
    public function getFreeCount(): int
    {
        return $this->chan->length();
    }

    /**
     * @return int
     */
    public function getBusyCount(): int
    {
        return $this->busyCount;
    }
}
