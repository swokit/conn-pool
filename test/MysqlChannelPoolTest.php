<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018-11-28
 * Time: 13:51
 */

namespace Swokit\Pool\Test;

use PHPUnit\Framework\TestCase;

/**
 * Class MysqlChannelPoolTest
 * @covers \Swokit\Pool\Mysql\ChannelDriverPool
 * @package Swokit\Pool\Test
 */
class MysqlChannelPoolTest extends TestCase
{
    protected function tearDown()
    {
        \Swoole\Timer::after(5 * 1000, function () {
            \swoole_event_exit();
        });
    }

    public function testQuery()
    {
        $cid = go(function () {
            // var_dump("CID: " . \Swoole\Coroutine::getuid());

            $pool = new \Swokit\Pool\Mysql\ChannelDriverPool();
            $pool->setOptions([
                'db' => [
                    'host' => 'localhost',
                    'port' => 13306,
                    'charset' => 'utf8',
                    'timeout' => 3,
                    'user' => 'root',
                    'password' => '123456',
                    'database' => 'mysql',
                ],
            ]);

            $db = $pool->get();

            // var_dump($pool->getMetas());
            $ret = $db->query('select * from `db` limit 1');
            var_dump("result:", $ret);

            $pool->put($db);

            // var_dump($pool->getMetas());
        });

        $this->assertTrue($cid > -1);
    }
}
