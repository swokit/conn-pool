<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/5/28
 * Time: 下午10:36
 */

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Asia/Shanghai');

$inhereDir = dirname(__DIR__, 2);
$map = [
    'Swokit\Pool\Test\\' => __DIR__,
    'Swokit\Pool\\' => dirname(__DIR__) . '/src',
];

spl_autoload_register(function ($class) use ($map) {
    foreach ($map as $np => $dir) {
        if (0 === strpos($class, $np)) {
            $path = str_replace('\\', '/', substr($class, strlen($np)));
            $file = $dir . "/{$path}.php";

            if (is_file($file)) {
                include_file($file);
            }
        }
    }
});

if (file_exists($file = dirname(__DIR__) . '/vendor/autoload.php')) {
    require $file;
} elseif (file_exists($file = dirname(__DIR__, 3) . '/autoload.php')) {
    require $file;
}

function include_file($file)
{
    include $file;
}

