<?php
/**
 * Created by PhpStorm.
 * User: baidu
 * Date: 18/3/27
 * Time: 上午12:50
 */
class Ws {
    CONST HOST = "0.0.0.0";
    CONST PORT = 8811;
    CONST CHART_PORT = 8812;
    CONST GAME_PORT = 8813;

    public $ws = null;
    public function __construct() {
        $this->ws = new swoole_websocket_server(self::HOST, self::PORT);
        $this->ws->listen(self::HOST, self::CHART_PORT, SWOOLE_SOCK_TCP);
        $this->ws->listen(self::HOST, self::GAME_PORT, SWOOLE_SOCK_TCP);

        $this->ws->set(
            [
                'enable_static_handler' => true,
                'document_root' => "/home/work/htdocs/swooleNBA/public/static",
                'worker_num' => 4,
                'task_worker_num' => 4,
            ]
        );

        $this->ws->on("start", [$this, 'onStart']);
        $this->ws->on("open", [$this, 'onOpen']);
        $this->ws->on("message", [$this, 'onMessage']);
        $this->ws->on("workerstart", [$this, 'onWorkerStart']);
        $this->ws->on("request", [$this, 'onRequest']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        $this->ws->on("close", [$this, 'onClose']);

        $this->ws->start();
    }

    /**
   * onStart
   *平滑重启设置别名
   */
    public function onStart($server) {
        // onStart调用时修改为主进程名称,方便重启脚本能够找到对应的pid
        swoole_set_process_name("live_master");
    }

    /**
     * 事件回调函数
     * 此事件在Worker进程/Task进程启动时发生
     * @param $server
     * @param $worker_id
     */
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

    /**
     * request回调
     * @param $request
     * @param $response
     */
    public function onRequest($request, $response) {

         //在这里设置图标默认状态码为404，为了让其请求不写入日志中
          if($request->server['query_string'] == 's=/favicon.ico')
          {
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

        $_POST['http_server'] = $this->ws;


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

    /**
     * @param $serv
     * @param $taskId
     * @param $workerId
     * @param $data
     */
    public function onTask($serv, $taskId, $workerId, $data) {

        // 分发 task 任务机制，让不同的任务 走不同的逻辑
        $obj = new app\common\lib\task\Task;

        $method = $data['method'];
        $flag = $obj->$method($data['data'], $serv);
        /*$obj = new app\common\lib\ali\Sms();
        try {
            $response = $obj::sendSms($data['phone'], $data['code']);
        }catch (\Exception $e) {
            // todo
            echo $e->getMessage();
        }*/

        return $flag; // 告诉worker
    }

    /**
     * @param $serv
     * @param $taskId
     * @param $data
     */
    public function onFinish($serv, $taskId, $data) {
        echo "taskId:{$taskId}\n";
        echo "finish-data-sucess:{$data}\n";
    }

    /**
     * 监听ws连接事件
     * @param $ws
     * @param $request
     */
    public function onOpen($ws, $request) {
        // fd redis [1]
        // 这里需要作一下区分,,8811端口的记录在redis,8812的不要
        /*foreach($_POST['http_server']->ports[1]->connections as $fd) {
            echo $fd.'------------'.PHP_EOL;
        }*/
        /*foreach ($this->ws->ports[2]->connections as $fd) {
          echo $fd.'------------'.PHP_EOL;
        }

        foreach ($this->ws->ports[1]->connections as $fd) {
          echo $fd.'------------'.PHP_EOL;
        }*/
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

        var_dump($request->fd);
    }

    /**
     * 监听ws消息事件
     * @param $ws
     * @param $frame
     */
    public function onMessage($ws, $frame) {
        echo "ser-push-message:{$frame->data}\n";
        $ws->push($frame->fd, "server-push:".date("Y-m-d H:i:s"));
    }

    /**
     * close
     * @param $ws
     * @param $fd
     */
    public function onClose($ws, $fd) {
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
    //echo $f.PHP_EOL.$log;
    }

    
}



new Ws();

// 20台机器    agent -> spark (计算) - 》 数据库   elasticsearch  hadoop

// sigterm sigusr1 usr2 信号源
