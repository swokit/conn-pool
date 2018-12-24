<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-08
 * Time: 15:11
 */

namespace Swokit\Pool\Mysql;

use Swokit\Pool\SleepWaitPool;
use Swoole\Coroutine\MySQL;

/**
 * Class CoMySQLPool2
 * @package Swokit\Pool\Mysql
 */
class SleepDriverPool extends SleepWaitPool
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
        $db->connect($config);

        return $db;
    }

    /**
     * 销毁资源实例
     * @param $resource
     * @return void
     */
    public function destroy($resource)
    {
//        unset($resource);
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
