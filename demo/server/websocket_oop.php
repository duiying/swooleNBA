<?php
/**
 * User: wyx
 * Desc: websocket服务
 */

class Ws {

    CONST HOST = "0.0.0.0";
    CONST PORT = 8812;

    public $ws = null;
    public function __construct() {
        $this->ws = new swoole_websocket_server("0.0.0.0", 8812);

        $this->ws->set(
            [
                'worker_num'        => 2,               // 设置worker进程数量
                'task_worker_num'   => 2,               // 设置Task进程数量
            ]
        );

        // 事件回调函数
        $this->ws->on("open", [$this, 'onOpen']);
        $this->ws->on("message", [$this, 'onMessage']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        $this->ws->on("close", [$this, 'onClose']);

        $this->ws->start();
    }

    // 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数
    public function onOpen($ws, $request) {
        var_dump($request->fd);

        // 异步毫秒定时器
        if ($request->fd == 1) {
            // 每2秒执行一次
            swoole_timer_tick(2000, function($timer_id){
                echo "2s: timerId:{$timer_id}\n";
            });
        }
    }

    // onMessage 当服务器收到来自客户端的数据帧时会回调此函数
    public function onMessage($ws, $frame) {
        echo "server-push-message:{$frame->data}\n";

        // 异步task开始
        // 需要时间 10s
        $data = [
            'task' => 1,
            'fd' => $frame->fd,
        ];
        $ws->task($data);
        // 异步task结束

        // 异步毫秒定时器 use的作用是连接闭包(匿名函数)和外界变量
        swoole_timer_after(5000, function() use ($ws, $frame) {
            echo "5s-after\n";
            $ws->push($frame->fd, "server-time-after");
        });

        // 这里的执行不会等待异步task耗时10s的任务结束之后才会执行,而是会立即执行
        $ws->push($frame->fd, "server-push:".date("Y-m-d H:i:s"));
    }

    // 在task_worker进程内被调用
    public function onTask($serv, $taskId, $workerId, $data) {
        print_r($data);
        // 耗时场景 10s
        sleep(10);
        return "on task finish"; // 告诉worker
    }

    // 当worker进程投递的任务在task_worker中完成时,task进程会通过swoole_server->finish()方法将任务处理的结果发送给worker进程
    public function onFinish($serv, $taskId, $data) {
        echo "taskId:{$taskId}\n";
        echo "finish-data-success:{$data}\n";
    }

    // TCP客户端连接关闭后,在worker进程中回调此函数
    public function onClose($ws, $fd) {
        echo "close-clientid:{$fd}\n";
    }
}

// 实例化
$obj = new Ws();