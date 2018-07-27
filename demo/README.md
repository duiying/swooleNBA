# demo
学习swoole的demo  

### 网络通信引擎
TCP/UDP/HTTP/websocket通信引擎
### TCP例程
#### 服务端 demo/server/tcp.php
![TCP](https://github.com/duiying/swooleNBA/blob/master/demo/readmeimg/tcp.png)
```
* 查看tcp.php的worker进程数(6个)
[root@VM_12_22_centos client]# ps aft | grep tcp.php
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
### UDP例程
#### 服务端 demo/server/udp.php 客户端 demo/client/udp_client.php
![UDP](https://github.com/duiying/swooleNBA/blob/master/demo/readmeimg/udp.png)
### HTTP例程
#### 服务端 demo/server/http.php
![UDP](https://github.com/duiying/swooleNBA/blob/master/demo/readmeimg/http.png)
### websocket例程
### 服务端 demo/server/websocket.php 客户端 demo/data/websocket_client.html
![websocket](https://github.com/duiying/swooleNBA/blob/master/demo/readmeimg/websocket.png)
