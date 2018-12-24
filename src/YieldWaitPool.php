<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-08
 * Time: 10:55
 */

namespace Swokit\Pool;

use Swoole\Coroutine;
use Toolkit\Pool\SPLQueuePool;

/**
 * Class YieldWaitPool - switch coroutine by Coroutine::yield()
 * - wait by coroutine switch. please see @link https://wiki.swoole.com/wiki/page/773.html
 * @package Swokit\Pool
 */
abstract class YieldWaitPool extends SPLQueuePool
{
    /**
     * @var \SplQueue
     * [
     *  CoroutineId0,
     *  CoroutineId1,
     *  CoroutineId2,
     * ... ...
     * ]
     */
    private $waitingQueue;

    protected function init()
    {
        $this->waitingQueue = new \SplQueue();

        parent::init();
    }

    /**
     * 等待并返回可用资源
     * @return bool|mixed
     */
    protected function wait()
    {
        $coId = Coroutine::getuid();

        // 保存等待的协程ID
        $this->waitingQueue->push($coId);

        // 无空闲资源可用，挂起当前协程
        Coroutine::yield();// alias Coroutine::suspend();

        // 恢复后， 返回可用资源
        return $this->getFreeQueue()->pop();
    }

    /**
     * {@inheritdoc}
     */
    public function put($resource)
    {
        parent::put($resource);

        // 有等待的协程
        if ($this->hasWaiting()) {
            $coId = $this->waitingQueue->pop();

            // 恢复等待的协程
            Coroutine::resume($coId);
        }
    }

    /**
     * @return int
     */
    public function countWaiting(): int
    {
        return $this->waitingQueue->count();
    }

    /**
     * @return bool
     */
    public function hasWaiting(): bool
    {
        return $this->waitingQueue->count() > 0;
    }
}
