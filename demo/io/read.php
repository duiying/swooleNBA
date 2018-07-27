<?php
$result = Swoole\Async::readfile(__DIR__."/1.txt", function($filename, $fileContent) {
    echo "filename:".$filename.PHP_EOL; 
    echo "content:".$fileContent.PHP_EOL;
});

// 打印读取结果
var_dump($result);
// 因为是异步,会先输出这行
echo "start".PHP_EOL;