<?php
/**
 * User: wyx
 * Desc: HTTP服务 & websocket服务
 */
class Ws {
    CONST HOST        = "0.0.0.0";      // 0.0.0.0表示监听所有地址
    CONST PORT        = 8811;           // 监听端口 8811作为HTTP服务端口
    CONST CHART_PORT  = 8812;           // 监听端口 8812作为websocket(聊天室)服务端口
    CONST GAME_PORT   = 8813;           // 监听端口 8813作为websocket(赛况)服务端口

    public $ws = null;
    public function __construct() {
        $this->ws = new swoole_websocket_server(self::HOST, self::PORT);
        $this->ws->listen(self::HOST, self::CHART_PORT, SWOOLE_SOCK_TCP);
        $this->ws->listen(self::HOST, self::GAME_PORT, SWOOLE_SOCK_TCP);

        $this->ws->set(
            [
                'enable_static_handler' => true,                                          // 使document_root配置生效
                'document_root'         => "/home/work/htdocs/swooleNBA/public/static",   // 配置静态文件根目录
                'worker_num'            => 4,                                             // 设置worker进程数量
                'task_worker_num'       => 4,                                             // 设置Task进程数量
            ]
        );

        // 事件回调函数
        $this->ws->on("start", [$this, 'onStart']);
        $this->ws->on("open", [$this, 'onOpen']);
        $this->ws->on("message", [$this, 'onMessage']);
        $this->ws->on("workerstart", [$this, 'onWorkerStart']);
        $this->ws->on("request", [$this, 'onRequest']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        $this->ws->on("close", [$this, 'onClose']);

        // 启动Server
        $this->ws->start();
    }

    // Server启动在主进程的主线程回调此函数
    public function onStart($server) {
        // onStart调用时修改主进程名称,方便重启脚本能够找到对应的pid
        swoole_set_process_name("live_master");
    }

    // Worker进程/Task进程启动时发生
    public function onWorkerStart($server,  $worker_id) {
        // 定义应用目录
        define('APP_PATH', __DIR__ . '/../../../application/');
        // 加载框架里面的文件
        require __DIR__ . '/../../../thinkphp/start.php';

        // 服务重启,先判断redis中该key是否有值,如果有就清空,目的是删除重启之前的fd
        $set = \app\common\lib\redis\Predis::getInstance()->sMembers(config("redis.live_user"));
        if (!empty($set)) {
            foreach ($set as $key => $value) {
                \app\common\lib\redis\Predis::getInstance()->sRem(config("redis.live_user"), $value);
            }
        }
    }

    // swoole_websocket_server 继承自 swoole_http_server
    // 设置了onRequest回调,websocket服务器也可以同时作为http服务器
    public function onRequest($request, $response) {

         //在这里设置图标默认状态码为404，为了让其请求不写入日志中
          if ($request->server['query_string'] == 's=/favicon.ico') {
            $response->status(404);
            //结束响应
            $response->end();
            return;
          }


        $_SERVER  =  [];
        if(isset($request->server)) {
            foreach($request->server as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        if(isset($request->header)) {
            foreach($request->header as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }

        $_GET = [];
        if(isset($request->get)) {
            foreach($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        }
        $_FILES = [];
        if(isset($request->files)) {
            foreach($request->files as $k => $v) {
                $_FILES[$k] = $v;
            }
        }
        $_POST = [];
        if(isset($request->post)) {
            foreach($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }

        // 记录日志
        $this->writeLog();

        // 传递ws对象
        $_POST['http_server'] = $this->ws;

        // 开启缓冲区
        ob_start();
        // 执行应用并响应
        try {
            think\Container::get('app', [APP_PATH])
                ->run()
                ->send();
        }catch (\Exception $e) {
            // todo
        }

        $res = ob_get_contents();
        ob_end_clean();
        $response->end($res);
    }

    // 在task_worker进程内被调用
    public function onTask($serv, $taskId, $workerId, $data) {
        $obj = new app\common\lib\task\Task;
        $method = $data['method'];
        // 分发task,让不同task走不同方法
        $flag = $obj->$method($data['data'], $serv);
        // 返回执行结果到worker进程,worker进程中会触发onFinish函数,表示投递的task已完成
        return $flag; 
    }

    // 当worker进程投递的任务在task_worker中完成时,task进程会通过swoole_server->finish()方法将任务处理的结果发送给worker进程
    public function onFinish($serv, $taskId, $data) {
        echo "taskId:{$taskId}\n";
        echo "finish-data-sucess:{$data}\n";
    }

    // 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数
    public function onOpen($ws, $request) {
        // 将8813端口的客户端websocket连接ID保存到redis
        $set = \app\common\lib\redis\Predis::getInstance()->sMembers(config("redis.live_user"));
        if (empty($set)) {
          foreach ($this->ws->ports[2]->connections as $fd) {
            \app\common\lib\redis\Predis::getInstance()->sAdd(config('redis.live_user'), $fd);
          }
        } else {
          foreach ($this->ws->ports[2]->connections as $fd) {
            if ($request->fd == $fd) {
              \app\common\lib\redis\Predis::getInstance()->sAdd(config('redis.live_user'), $fd);
              break;
            }
          }
        }
    }

    // onMessage 当服务器收到来自客户端的数据帧时会回调此函数
    public function onMessage($ws, $frame) {
        echo "ser-push-message:{$frame->data}\n";
        // 向websocket客户端连接推送数据
        $ws->push($frame->fd, "server-push:".date("Y-m-d H:i:s"));
    }

    // TCP客户端连接关闭后,在worker进程中回调此函数
    public function onClose($ws, $fd) {
      // 从redis中删除8813端口的客户端websocket断开连接ID
      $set = \app\common\lib\redis\Predis::getInstance()->sMembers(config("redis.live_user"));
      if (in_array($fd, $set)) {
        \app\common\lib\redis\Predis::getInstance()->sRem(config('redis.live_user'), $fd);
      }
      echo "clientid:{$fd}\n";
    }

    // 记录日志
    public function writeLog() {
      //封装数组
      $datas = array_merge($_POST,$_GET,$_SERVER);
      $log = date("Ymd H:i:s")." ";
      //连成字符存
      foreach ($datas as $key => $value) {
        $log.= $key.":".$value."  ";
      }
      //使用异步写文件
      $f=swoole_async_writeFile(APP_PATH.'../runtime/log/'.date('Ym').'/'.date('d').'_accessinfo.log', $log.PHP_EOL, function($filename) {
      //todo
   
      }, FILE_APPEND);
    }
    
}

// 实例化
new Ws();

