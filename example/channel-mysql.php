<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018/3/1
 * Time: 下午5:36
 */

require dirname(__DIR__) . '/test/boot.php';

go(function () {
    var_dump("CID: " . \Swoole\Coroutine::getuid());

    $pool = new \SwoKit\Pool\Mysql\ChannelDriverPool();
    $pool->setInitSize(3);
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

    // will prepare $initSize resources.
    $pool->initPool();

    var_dump($pool->getMetas());
    $db = $pool->get();

    var_dump($pool->getMetas());
    $ret = $db->query('select * from `db` limit 1');
    var_dump("result:", $ret);

    $pool->put($db);

    var_dump($pool->getMetas());

    var_dump($pool->count());
});

\Swoole\Timer::after(5 * 1000, function () {
    echo "end\n";
    swoole_event_exit();
});