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
                'worker_num' => 2,
                'task_worker_num' => 2,
            ]
        );
        $this->ws->on("open", [$this, 'onOpen']);
        $this->ws->on("message", [$this, 'onMessage']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        $this->ws->on("close", [$this, 'onClose']);

        $this->ws->start();
    }

    /**
     * 监听ws连接事件
     * @param $ws
     * @param $request
     */
    public function onOpen($ws, $request) {
        var_dump($request->fd);

        // 异步毫秒定时器
        if($request->fd == 1) {
            // 每2秒执行
            swoole_timer_tick(2000, function($timer_id){
                echo "2s: timerId:{$timer_id}\n";
            });
        }
    }

    /**
     * 监听ws消息事件
     * @param $ws
     * @param $frame
     */
    public function onMessage($ws, $frame) {
        echo "ser-push-message:{$frame->data}\n";

        // 异步task开始
        // 假如说需要时间 10s
        $data = [
            'task' => 1,
            'fd' => $frame->fd,
        ];
        // $ws->task($data);
        // 异步task结束

        // 异步毫秒定时器 use使用的是php递包
        swoole_timer_after(5000, function() use($ws, $frame) {
            echo "5s-after\n";
            $ws->push($frame->fd, "server-time-after:");
        });

        // 这里的执行不会等待10s的任务结束之后才会执行,而是会立即执行
        $ws->push($frame->fd, "server-push:".date("Y-m-d H:i:s"));
    }

    /**
     * @param $serv
     * @param $taskId
     * @param $workerId
     * @param $data
     */
    public function onTask($serv, $taskId, $workerId, $data) {
        print_r($data);
        // 耗时场景 10s
        sleep(10);
        return "on task finish"; // 告诉worker
    }

    /**
     * @param $serv
     * @param $taskId
     * @param $data onTask()中return的内容
     */
    public function onFinish($serv, $taskId, $data) {
        echo "taskId:{$taskId}\n";
        echo "finish-data-sucess:{$data}\n";
    }

    /**
     * close
     * @param $ws
     * @param $fd
     */
    public function onClose($ws, $fd) {
        echo "clientid:{$fd}\n";
    }
}

$obj = new Ws();