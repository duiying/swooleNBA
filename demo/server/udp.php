<?php

// 创建一个Server对象,监听IP为127.0.0.1,监听端口为9501,类型为SWOOLE_SOCK_UDP
$serv = new swoole_server("127.0.0.1", 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

$serv->set([
    'worker_num' 	=> 6,			// worker进程数,cpu核数的1-4倍 
    'max_request' 	=> 10000,		// worker进程在处理完n次请求后结束运行,manager会重新创建一个worker进程,此选项用来防止worker进程内存溢出
]);

// 事件回调函数,接收到UDP数据包时回调此函数
$serv->on('Packet', function ($serv, $data, $clientInfo) {
    $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$data);
    var_dump($clientInfo);
});

// 启动server
$serv->start();