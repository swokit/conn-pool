<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-08
 * Time: 15:11
 */

namespace SwooleLib\Pool\Co\MySQL;

use Swoole\Coroutine\MySQL;
use SwooleLib\Pool\Co\SuspendWaitPool;

/**
 * Class CoMySQLPool
 * @package SwooleLib\Pool\Co\MySQL
 */
class MySQLPool extends SuspendWaitPool
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
     */
    public function create()
    {
        $conf = $this->options['db1'];
        $db = new MySQL();

        // debug('coId:' . Coroutine::id() . ' will create new db connection');

        $db->connect($conf);

        // debug('coId:' . Coroutine::id() . ' a new db connection created');

        return $db;
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
        $obj->query('SELECT 1');
    }
}
