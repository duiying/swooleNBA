# 服务监控脚本

# nohup 使命令不挂断地运行
# nohup 并没有后台运行的功能,断开SSH连接不会停止它的运行,但是可以ctrl+c可以使其停止运行
# & 	使nohup在后台运行,ctrl+c不能使其停止运行,只能通过kill来关闭进程

# 如何停止脚本
#	[root@VM_12_22_centos monitor]# ps -ef | grep monitor/server.php
#	root     12184     1  0 20:52 ?        00:00:00 /home/work/study/soft/php/bin/php /home/work/htdocs/swooleNBA/script/monitor/server.php
#	root     13843 12281  0 20:58 pts/3    00:00:00 grep --color=auto monitor/server.php
#	[root@VM_12_22_centos monitor]# kill 12184

nohup /home/work/study/soft/php/bin/php /home/work/htdocs/swooleNBA/script/monitor/server.php > /home/work/htdocs/swooleNBA/script/monitor/monitor.log &