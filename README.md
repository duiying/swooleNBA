# SwooleNBA
ThinkPHP5整合原生Swoole实现NBA比赛图文直播以及聊天室功能
### 效果图
##### 登录页面(访问地址: http://yourIP/admin/live.html)
![登录](https://github.com/duiying/swooleNBA/blob/master/readmeimg/login.png)
##### 聊天室页面(访问地址: http://yourIP/live/detail.html)
![聊天室](https://github.com/duiying/swooleNBA/blob/master/readmeimg/chart.png)

### 环境安装
```
* 首先安装必要的工具以及依赖
    yum -y install gcc gcc-c++ libxml2 libxml2-devel git autoconf telnet pcre-devel curl-devel
* 源码包统一放在/usr/src/目录
* 安装PHP7
    * 下载地址
        http://php.net/get/php-7.2.2.tar.gz/from/a/mirror
    * 进入目录  
        cd /usr/src/
    * 解压文件
        tar -xzvf php-7.2.2.tar.gz
    * 进入目录
        cd php-7.2.2/
    * 配置
        ./configure --prefix=/home/work/study/soft/php --with-curl
    * 编译
        make
    * 安装
        make install
    * 将PHP命令加入到环境变量
        vi ~/.bash_profile
        * 在最后增加一行
            alias php=/home/work/study/soft/php/bin/php
        * 使配置文件生效
            source ~/.bash_profile
        * 使用PHP命令检查PHP版本
            [root@VM_12_22_centos /]# php -v
            PHP 7.2.2 (cli) (built: Jul 19 2018 10:58:51) ( NTS )
            Copyright (c) 1997-2018 The PHP Group
            Zend Engine v3.2.0, Copyright (c) 1998-2018 Zend Technologies
        * 将源码包下的php.ini拷贝到PHP安装目录,并且重命名
            [root@VM_12_22_centos swoole]# cd /usr/src/php-7.2.2/
            [root@VM_12_22_centos php-7.2.2]# cp php.ini-development /home/work/study/soft/php/etc/
            [root@VM_12_22_centos php-7.2.2]# cd /home/work/study/soft/php/etc/
            [root@VM_12_22_centos etc]# mv php.ini-development php.ini
        * 查看php.ini文件的生效目录并移动php.ini到该目录
            [root@VM_12_22_centos etc]# php -i | grep php.ini
            Configuration File (php.ini) Path => /home/work/study/soft/php/lib
            [root@VM_12_22_centos etc]# mv /home/work/study/soft/php/etc/php.ini /home/work/study/soft/php/lib/
            
* 安装redis
    * 进入目录
        cd /usr/src/
    * 下载
        wget http://download.redis.io/releases/redis-4.0.2.tar.gz
    * 解压文件
        tar -xzvf redis-4.0.2.tar.gz
    * 进入目录
        cd redis-4.0.2/
    * 编译
        make
    * 安装
        make install
    * 检查是否安装成功
        * 进入目录
            /usr/src/redis-4.0.2/src
        * 启动redis服务端
            ./redis-server
* 启用协程redis客户端
    * 需要安装一个第三方的异步redis库hiredis
        * 进入目录
            cd /usr/src/
        * 下载
            wget https://github.com/redis/hiredis/archive/v0.13.3.tar.gz
        * 解压文件
            tar -xzvf v0.13.3.tar.gz
        * 进入目录
            cd hiredis-0.13.3/
        * 编译
            make
        * 安装
            make install
        * 执行动态链接库管理命令
            ldconfig
* 安装swoole
    * 进入目录
        cd /usr/src/
    * 下载
        git clone https://gitee.com/swoole/swoole.git
    * 进入目录
        cd swoole/
    * 生成configure文件
        /home/work/study/soft/php/bin/phpize
    * 配置
        ./configure --with-php-config=/home/work/study/soft/php/bin/php-config --enable-async-redis
    * 编译
        make
    * 安装
        make install
    * 修改php.ini,新增一行extension=swoole(也有可能是extension=swoole.so)
        vim /home/work/study/soft/php/lib/php.ini
    * 可能遇到的问题
        * php-m 发现swoole消失或者是通过php --ri swoole没有显示async redis client
        vi ~/.bash_profile
        在最后一行添加 export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/usr/local/lib
        source ~/.bash_profile
        * 重新编译安装swoole
            * 清除上次的make命令所产生的object文件(后缀为".o"的文件)及可执行文件
                make clean
            * 编译
                make
            * 安装
                make install
    * 使用php -m命令查看PHP的扩展发现有swoole,说明swoole扩展安装成功
    * 使用php --ri swoole发现显示内容中有async redis client => enabled,说明swoole安装异步redis客户端成功
* PHP安装redis扩展
    * 进入目录
        cd /usr/src/
    * 下载
        git clone https://github.com/phpredis/phpredis
    * 进入目录
        cd phpredis/
    * 生成configure文件
        /home/work/study/soft/php/bin/phpize
    * 配置
        ./configure --with-php-config=/home/work/study/soft/php/bin/php-config
    * 编译
        make
    * 安装
        make install
    * 修改php.ini,新增一行extension=redis(也有可能是extension=redis.so)
        vim /home/work/study/soft/php/lib/php.ini
    * 使用php -m命令查看PHP的扩展发现有redis,说明redis扩展安装成功
* 安装Nginx
    * yum方式安装
        rpm -Uvh http://nginx.org/packages/centos/7/noarch/RPMS/nginx-release-centos-7-0.el7.ngx.noarch.rpm
        yum install nginx
    * 启动
        service nginx start
    * 停止
        service nginx stop
    * 重启
        service nginx restart
    * 重载
        service nginx reload
```

### Nginx配置
```
* 进入目录
    [root@VM_12_22_centos conf.d]# cd /etc/nginx/conf.d/
* 删除default.conf,新建文件swoole.conf
    [root@VM_12_22_centos conf.d]# ls
    swoole.conf
* 编辑swoole.conf文件,内容如下
    [root@VM_12_22_centos conf.d]# cat swoole.conf 
    server
        {
            listen 80;
            #listen [::]:80;

            location / {
                root   /home/work/htdocs/swooleNBA/public/static;
                index  index.html index.htm;
                if ($uri = /) {
                    proxy_pass http://127.0.0.1:8811;
                }
                if (!-e $request_filename) {
                     rewrite ^(.*)$ /index.php?s=$1;
                     proxy_pass http://127.0.0.1:8811;
                     break;
                }
            }
    }
```
### 导入并配置工程
```
* 在/home/work/htdocs目录下导入swooleNBA工程
* 启动redis服务端
    * 进入目录
        /usr/src/redis-4.0.2/src
    * 启动redis服务端
        ./redis-server
* 启动服务监控脚本
    * Windows下写好的shell脚本上传到linux,执行会报错 ./monitor.sh: line 2: $'\r': command not found
    * 为了解决该问题,需要先对文件进行转义,然后再执行脚本
    * 需要安装dos2unix yum -y install dos2unix
    * 进入shell脚本所在目录
        [root@VM_12_22_centos monitor]# cd /home/work/htdocs/swooleNBA/script/monitor
    * 更改文件权限
        [root@VM_12_22_centos monitor]# chmod 777 *.sh
    * 对shell脚本进行转义
        [root@VM_12_22_centos monitor]# dos2unix monitor.sh 
        dos2unix: converting file monitor.sh to Unix format ...
    * 执行shell脚本
        [root@VM_12_22_centos monitor]# ./monitor.sh 
        [root@VM_12_22_centos monitor]# nohup: redirecting stderr to stdout
    * 使用tail -f命令实时观测监控结果
        [root@VM_12_22_centos monitor]# tail -f /home/work/htdocs/swooleNBA/script/monitor/monitor.log
    * 通过kill命令可以停止脚本,具体实现可参考/home/work/htdocs/swooleNBA/script/monitor/monitor.sh中的注释
* 服务启动脚本与平滑重启脚本
    * 进入shell脚本所在目录
        [root@VM_12_22_centos server]# cd /home/work/htdocs/swooleNBA/script/bin/server
    * 更改文件权限
        [root@VM_12_22_centos server]# chmod 777 *.sh
    * 对shell脚本进行转义
        [root@VM_12_22_centos server]# dos2unix *.sh
        dos2unix: converting file reload.sh to Unix format ...
        dos2unix: converting file start.sh to Unix format ...
    * 启动服务
        [root@VM_12_22_centos server]# ./start.sh
* 阿里云短信API配置
    * 在swooleNBA/config目录下新建文件alisms.php,内容如下
        <?php
            return [
                'accessKeyId'               => 'AccessKeyId',        
                'accessKeySecret'           => 'AccessKeySecret',
                'SignName'                  => '签名名称',      
                'TemplateCode'              => '模板CODE'         
            ];
```





### 可能用到的linux命令
```
* 启动一个进程时提示端口已被占用
* 解决方法
    1.查找占用指定端口的进程
        比如 netstat -anp | grep 8811
    2.kill 进程id(即pid)
        比如 kill 21656
```

### todo
```
* 聊天室采用真正的登录用户聊天
* 用websocket完善
```