<?php

$server = new swoole_websocket_server("0.0.0.0", 8812);

// 事件回调函数,当WebSocket客户端与服务器建立连接并完成握手后会回调此函数
$server->on('open', 'onOpen');

function onOpen($server, $request) {
    print_r($request->fd);
}

// 事件回调函数,当服务器收到来自客户端的数据帧时会回调此函数
$server->on('message', function (swoole_websocket_server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "push-success");
});

$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});

// 启动server
$server->start();