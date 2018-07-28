<?php

echo "process-start-time: ".date("Ymd H:i:s").PHP_EOL;
$workers    = [];
$urls       = [
    'http://baidu.com?wd=test1',
    'http://baidu.com?wd=test2',
    'http://baidu.com?wd=test3',
    'http://baidu.com?wd=test4',
    'http://baidu.com?wd=test5',
    'http://baidu.com?wd=test6',
];

for($i = 0; $i < 6; $i++) {
    // 子进程
    $process = new swoole_process(function(swoole_process $worker) use($i, $urls) {
        // curl
        $content = curlData($urls[$i]);
        // 向管道内写入数据
        $worker->write($content.PHP_EOL);
    }, true);

    // 创建成功返回子进程的PID
    $pid = $process->start();
    $workers[$pid] = $process;
}

foreach($workers as $process) {
    // 从管道中读取数据
    echo $process->read();
}

// 模拟curl请求
function curlData($url) {
    sleep(1);
    return $url . " success".PHP_EOL;
}

echo "process-end-time:".date("Ymd H:i:s").PHP_EOL;