<?php
// 连接 swoole tcp 服务
$client = new swoole_client(SWOOLE_SOCK_UDP);

if (!$client->connect("127.0.0.1", 9502)) {
    echo "connect failed";
    exit;
}

// STDOUT/STDIN => php cli常量
fwrite(STDOUT, "input data:");
$msg = trim(fgets(STDIN));

// 发送数据到远程服务器
$client->send($msg);

// 从服务器端接受数据
$result = $client->recv();

echo $result;