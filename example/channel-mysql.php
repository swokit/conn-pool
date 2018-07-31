<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018/3/1
 * Time: 下午5:36
 */

require dirname(__DIR__) . '/test/boot.php';

go(function () {
    $pool = new \SwooleKit\Pool\Mysql\ChannelDriverPool();
    $pool->setOptions([
        'db' => [
            'charset' => 'utf8',
            'port' => 3306,
            'timeout' => 3,
        ],
    ]);

    $db = $pool->get();

    $ret = $db->query('select * from tuserreport limit 1');
    // var_dump($ret);

    $pool->put($db);
});
