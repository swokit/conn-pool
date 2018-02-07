<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/2/6 0006
 * Time: 20:42
 */

namespace SwooleLib\Pool;

use Inhere\Pool\AbstractPool;
use Swoole\Timer;

/**
 * Class SwoolePool
 * @package Inhere\Pool
 */
abstract class SwoolePool extends AbstractPool
{
    /**
     * @var int
     */
    protected $checkInterval = 5;

    /**
     * @var array 用于检查空闲时间的定时器列表
     */
    protected $timers = [];

    /**
     * pool checker
     */
    public function poolChecker()
    {
        // 空闲时间超时检查
        Timer::tick($this->checkInterval * 1000, function () {

        });
    }
}
