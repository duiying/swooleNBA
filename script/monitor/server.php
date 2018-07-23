<?php 
/**
 * User: wyx
 * Desc: 利用shell进行服务监控
 */
class Server {
  // 端口
  const port  = 8811;

  public function port() {
    // 2>/dev/null => 过滤掉原本前面的文字
    // grep LISTEN |wc -l => 统计输出信息的行数 
    $shell    = 'netstat -anp 2>/dev/null | grep '.self::port." | grep LISTEN | wc -l";
    // 执行shell 如果为1说明8811端口被监听
    $result   = shell_exec($shell);
    if ($result == 1) {
      // 输出正确信息
    	echo "success".PHP_EOL;
    } else {
    	// 输出错误信息
    	echo date("Y-m-d H:i:s",time())."server error ".PHP_EOL;
    }
  }
}

// 使用swoole的异步毫秒定时器,每间隔2s执行一次
swoole_timer_tick(2000, function() {
  (new Server())->port();
});