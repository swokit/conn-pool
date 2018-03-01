<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018/3/1
 * Time: 下午5:36
 */

require dirname(__DIR__) . '/test/boot.php';

$config = [
    'options' => [
        [
            'host' => 'rm-uf6q9692z6o0u71ec.mysql.rds.aliyuncs.com',
            'port' => 3306,
            'user' => 'gameva',
            'password' => 'v8hdDTJy3c2ri5YB',
            'database' => 'test',
        ]
    ]
];

$pool = new \SwooleLib\Pool\Co\MySQL\SleepDriverPool($config);

\Swoole\Coroutine::create(function () use ($pool) {
    $pool->initPool();

    /** @var \Swoole\Coroutine\Mysql $db */
    $db = $pool->get();
    $ret = $db->query('show tables');
    $pool->put($db);

    var_dump($ret);

    swoole_event_exit();
});
