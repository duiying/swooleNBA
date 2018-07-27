<?php
// FILE_APPEND => 追加
$content = date("Ymd H:i:s").PHP_EOL;
// 异步写文件,调用此函数后会立即返回,当写入完成时会自动回调指定的callback函数
swoole_async_writefile(__DIR__."/1.log", $content, function($filename){
    // todo
    echo "success".PHP_EOL;
}, FILE_APPEND);

// 因为是异步,会先输出这行
echo "start".PHP_EOL;