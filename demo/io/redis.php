<?php

$redisClient = new swoole_redis;
$redisClient->connect('127.0.0.1', 6379, function(swoole_redis $redisClient, $result) {
    echo "connect".PHP_EOL;
    var_dump($result);
    echo '------------------------------'.PHP_EOL;

    $redisClient->set('wyx', time(), function(swoole_redis $redisClient, $result) {
        var_dump($result);
        echo '------------------------------'.PHP_EOL;
    });

    $redisClient->get('wyx', function(swoole_redis $redisClient, $result) {
        var_dump($result);
        echo '------------------------------'.PHP_EOL;
        $redisClient->close();
    });

    // 所有的key
    $redisClient->keys('*', function(swoole_redis $redisClient, $result) {
        var_dump($result);
        echo '------------------------------'.PHP_EOL;
    });

    // 模糊匹配
    $redisClient->keys('*wy*', function(swoole_redis $redisClient, $result) {
        var_dump($result);
        echo '------------------------------'.PHP_EOL;
        $redisClient->close();
    });
});

echo "start".PHP_EOL;