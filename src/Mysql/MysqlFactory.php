<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018/3/1
 * Time: 下午5:44
 */

namespace SwoKit\Pool\Mysql;

use Toolkit\Pool\FactoryInterface;
use Swoole\Coroutine\Mysql;

/**
 * Class MysqlFactory
 * @package SwoKit\Pool\Mysql
 */
class MysqlFactory implements FactoryInterface
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
     * @param \stdClass|mixed $obj The resource
     * @return mixed
     */
    public function destroy($obj)
    {
        // TODO: Implement destroy() method.
    }

    /**
     * @param \stdClass|mixed $obj The resource
     * @return bool
     */
    public function validate($obj): bool
    {
        // TODO: Implement validate() method.
    }
}
