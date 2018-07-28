<?php
$redis = new Swoole\Coroutine\Redis();
$redis->connect('127.0.0.1', 6379);
$redis->set('key', 'val');
echo $redis->get('key');