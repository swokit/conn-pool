<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018/3/1
 * Time: 下午5:23
 */

namespace SwoKit\Pool\Mysql;

use Toolkit\Pool\AbstractPool;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\MySQL;
use SwoKit\Pool\Exception\ChannelException;

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
     *  CoroutineId0,
     *  CoroutineId1,
     *  CoroutineId2,
     * ... ...
     * ]
     */
    private $chan;

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

        parent::init();
    }

    /**
     * 创建新的资源实例
     * @return mixed
     * @throws \Exception
     */
    public function create()
    {
        $count = \count($this->options);

        if ($count === 1) {
            $config = \array_values($this->options)[0];
        } else {
            $index = \random_int(0, $count - 1);
            $config = \array_values($this->options)[$index];
        }

        $db = new MySQL();
        if (!$db->connect($config)) {
            throw new \RuntimeException('connect to database server failure. info=' . \json_encode($config));
        }

        // add metadata
        $id = $this->genID($db);
        $this->metas[$id] = [
            'createAt' => \time(),
            'activeAt' => \time(),
        ];

        return $db;
    }

    /**
     * @return mixed|MySQL
     * @throws \Exception
     */
    public function get()
    {
        // get from channel
        if (!$db = $this->chan->pop($this->waitTimeout / 1000)) {
            $db = $this->create();

            // check connection
        } elseif (!$this->validate($db)) {
            $db = $this->create();
        }

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

        // if lost connection
        if (!$obj->connected) {
            $ok = $obj->connect($obj->serverInfo);

            if (!$ok) {
                $this->destroy($obj);
                return false;
            }
        }

        return true;
    }

    public function clear()
    {
        $timeout = $this->waitTimeout / 1000;
        while ($db = $this->chan->pop($timeout)) {
            $this->destroy($db);
        }

        $this->chan->close();
    }

    /**
     * 等待并返回可用资源
     * @return bool|mixed
     */
    protected function wait()
    {
        // TODO: Implement wait() method.
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
}
