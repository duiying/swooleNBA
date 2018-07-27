# demo
学习swoole的demo  
## 环境安装
```
* 安装MySQL
	* 进入目录
    	cd /usr/src/
	* 下载MySQL源
    	wget http://dev.mysql.com/get/mysql57-community-release-el7-7.noarch.rpm
    * 安装MySQL源
    	rpm -ivh mysql57-community-release-el7-7.noarch.rpm
    * 安装mysql-community-server
    	yum -y install mysql-community-server
    * 启动MySQL服务
    	service mysqld start
    * 查看root密码
    	cat /var/log/mysqld.log | grep password
    * 用查出的密码登录
    	mysql -u root -p
    * 修改密码
    	set password = password('WYX*wyx123');
    * 开放远程连接
    	use mysql;
		update user set host = '%' where host = 'localhost' and user = 'root';
		flush privileges;
    * 创建名称为swoole的数据库,并执行下面sql语句
    	DROP TABLE IF EXISTS `test`;
        CREATE TABLE `test` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `username` varchar(100) NOT NULL,
          `create_time` int(10) unsigned NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `id` (`id`),
          KEY `username` (`username`)
        ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

        INSERT INTO `test` VALUES ('1', 'test1', '1520763235');
        INSERT INTO `test` VALUES ('2', 'test2', '1520763235');
        INSERT INTO `test` VALUES ('3', 'test3', '1520763235');
```
## 网络通信引擎
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
### websocket面向对象&task机制&毫秒定时器 例程
### 服务端 demo/server/websocket_oop.php 客户端 demo/data/websocket_client.html
![websocket](https://github.com/duiying/swooleNBA/blob/master/demo/readmeimg/websocket_oop.png)
## 异步非阻塞IO
### 异步读取文件内容 demo/io/read.php
```
[root@VM_12_22_centos io]# php read.php 
bool(true)
start
filename:/home/work/htdocs/swooleNBA/demo/io/1.txt
content:filedata
```
### 异步写文件
```
[root@VM_12_22_centos io]# pwd
/home/work/htdocs/swooleNBA/demo/io
[root@VM_12_22_centos io]# php write.php 
start
success
[root@VM_12_22_centos io]# cat 1.log 
20180727 12:38:56
```