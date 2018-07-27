<?php

// 创建一个Server对象,监听IP为127.0.0.1,监听端口为9501
$serv = new swoole_server("127.0.0.1", 9501);

$serv->set([
    'worker_num' 	=> 6,			// worker进程数,cpu核数的1-4倍 
    'max_request' 	=> 10000,		// worker进程在处理完n次请求后结束运行,manager会重新创建一个worker进程,此选项用来防止worker进程内存溢出
]);

// 事件回调函数,有新的连接进入时,在worker进程中回调
// $serv => Swoole\Server对象 $fd => 连接的文件描述符 $reactor_id => 来自哪个Refactor线程
$serv->on('connect', function ($serv, $fd, $reactor_id) {
    echo "ClientConnect: {$reactor_id} - {$fd} - Connect.\n";
});

// 事件回调函数,接收到数据时回调此函数,在worker进程中回调
$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    $serv->send($fd, "ServerReceive: {$reactor_id} - {$fd} - ".$data);
});

// 事件回调函数,TCP连接关闭后,在worker进程中回调
$serv->on('close', function ($serv, $fd) {
    echo "ClientClose: {$fd} - Close\n";
});

// 启动server
$serv->start();