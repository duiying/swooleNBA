<?php

$process = new swoole_process(function(swoole_process $pro) {
    $pro->exec("/home/work/study/soft/php/bin/php", [__DIR__.'/../io/read.php']);
}, false);

// 执行fork系统调用,启动进程
$pid = $process->start();
echo $pid . PHP_EOL;

// 回收结束运行的子进程
swoole_process::wait();
