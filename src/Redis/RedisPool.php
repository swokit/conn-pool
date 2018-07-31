<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-08
 * Time: 15:11
 */

namespace SwooleKit\Pool\Redis;

use SwooleKit\Pool\SuspendWaitPool;
use Swoole\Coroutine\Redis;

/**
 * Class CoRedisPool
 * @package SwooleKit\Pool\Redis
 */
class RedisPool extends SuspendWaitPool
{
    /**
     * 创建新的资源实例
     * @return mixed
     */
    public function create()
    {
        $rds = new Redis();
        // debug('coId:' . Coroutine::id() . ' will create new redis connection');
        $rds->connect('redis', 6379);

        // debug('coId:' . Coroutine::id() . ' a new redis connection created');
        return $rds;
    }

    /**
     * 销毁资源实例
     * @param $resource
     * @return void
     */
    public function destroy($resource)
    {
        // unset($resource);
    }

    /**
     * 验证资源(eg. db connection)有效性
     * @param mixed $obj
     * @return bool
     */
    protected function validate($obj): bool
    {
        // TODO: Implement validate() method.
    }
}
