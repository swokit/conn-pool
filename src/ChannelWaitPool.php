<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/2/7 0007
 * Time: 23:10
 */

namespace SwooleKit\Pool;

use Inhere\Pool\AbstractPool;
use Swoole\Channel;

/**
 * Class ChannelWaitPool
 * - wait by channel. please see
 * @link https://wiki.swoole.com/wiki/page/p-coroutine_channel.html
 * @package SwooleKit\Pool
 */
abstract class ChannelWaitPool extends AbstractPool
{
    /**
     * @var Channel
     * [
     *  CoroutineId0,
     *  CoroutineId1,
     *  CoroutineId2,
     * ... ...
     * ]
     */
    private $channel;

    protected function init()
    {
        $this->channel = new Channel(100);

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        // 创建一个就 push一个到 chan
        $this->channel->push(1);
    }

    /**
     * 等待并返回可用资源
     * @return bool|mixed
     */
    protected function wait()
    {
        // 检查是否有资源可用，没有就会等待
        $this->channel->pop();

        return $this->getFreeQueue()->pop();
    }

    /**
     * {@inheritdoc}
     */
    public function put($resource)
    {
        parent::put($resource);

        // 表明有资源可用
        $this->channel->push(1);
    }

    /**
     * @return int
     */
    public function waitingCount(): int
    {
        return $this->channel->stats()['queue_num'];
    }

    /**
     * @return bool
     */
    public function hasWaiting(): bool
    {
        return $this->waitingCount() > 0;
    }
}
