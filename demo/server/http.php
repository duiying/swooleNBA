<?php
$http = new swoole_http_server("0.0.0.0", 8811);

$http->set(
    [
        'enable_static_handler' => true,                                        // 使document_root配置生效
        'document_root'         => "/home/work/htdocs/swooleNBA/demo/data",     // 配置静态文件根目录
    ]
);

$http->on('request', function($request, $response) {
    $content = [
        'date:'     => date("Ymd H:i:s"),
        'get:'      => $request->get,
        'post:'     => $request->post,
        'header:'   => $request->header,
    ];

    // 记录日志-异步写文件
    swoole_async_writefile(__DIR__."/access.log", json_encode($content).PHP_EOL, function($filename){
        // todo
    }, FILE_APPEND);

    // 向响应里面写cookie 
    $response->cookie("cookieKey", "cookieVal", time() + 1800);

    // 向浏览器输出字符串
    $response->end("get: ". json_encode($request->get));
});

// 启动 server
$http->start();