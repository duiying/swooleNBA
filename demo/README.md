# demo
学习swoole的demo  

### 网络通信引擎
TCP/UDP/HTTP/websocket通信引擎
### TCP
#### 服务端 demo/server/tcp.php
![TCP](https://github.com/duiying/swooleNBA/blob/master/demo/readmeimg/tcp.png)
```
* 查看tcp.php的worker进程数(6个)
[root@VM_12_22_centos client]# php tcp_client.php 
input data:mydata
ServerReceive: 0 - 1 - mydata[root@VM_12_22_centos client]# ps aft | grep tcp.php
24684 pts/2    S+     0:00  \_ grep --color=auto tcp.php
24652 pts/0    Sl+    0:00  \_ /home/work/study/soft/php/bin/php tcp.php
24653 pts/0    S+     0:00      \_ /home/work/study/soft/php/bin/php tcp.php
24655 pts/0    S+     0:00          \_ /home/work/study/soft/php/bin/php tcp.php
24656 pts/0    S+     0:00          \_ /home/work/study/soft/php/bin/php tcp.php
24657 pts/0    S+     0:00          \_ /home/work/study/soft/php/bin/php tcp.php
24658 pts/0    S+     0:00          \_ /home/work/study/soft/php/bin/php tcp.php
24659 pts/0    S+     0:00          \_ /home/work/study/soft/php/bin/php tcp.php
24660 pts/0    S+     0:00          \_ /home/work/study/soft/php/bin/php tcp.php
```
#### 客户端 demo/client/tcp_client.php
![TCP](https://github.com/duiying/swooleNBA/blob/master/demo/readmeimg/tcp_client.png)
