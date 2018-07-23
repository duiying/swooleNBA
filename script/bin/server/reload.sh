# 平滑重启脚本

echo "loading...";
# pidof命令用于查找指定名称的进程的进程id
pid=`pidof live_master`;
echo $pid;
# 平滑重启所有worker进程 kill -USR1 主进程PID
kill -USR1 $pid;
echo "success restart";