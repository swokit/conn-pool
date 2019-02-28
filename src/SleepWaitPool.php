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
 * Class ResourcePool
 * - wait by coroutine sleep. please see @link https://wiki.swoole.com/wiki/page/784.html
 * @package Swokit\Pool
 */
abstract class SleepWaitPool extends SPLQueuePool
{
    /**
     * check Interval time(ms)
     * @var int
     */
    protected $checkInterval = 20;

    /**
     * 等待并返回可用资源
     * @return bool|mixed
     */
    protected function wait()
    {
        $timer          = 0;
        $timeout        = $this->getWaitTimeout();
        $interval       = $this->checkInterval;
        $intervalSecond = $this->checkInterval / 1000;

        while ($timer <= $timeout) {
            // 等到了可用的空闲资源
            if ($res = $this->getFreeQueue()->pop()) {
                return $res;
            }

            $timer += $interval;
            // 无空闲资源可用， 进入等待状态
            Coroutine::sleep($intervalSecond);
        }

        throw new \RuntimeException("No resources available. Waiting timeout($timeout ms) for get resource.");
    }
}
