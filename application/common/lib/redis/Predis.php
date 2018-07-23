<?php
/**
 * User: wyx
 * Desc: 使用phpredis封装redis基础类库(phpredis是php的一个扩展)
 */

namespace app\common\lib\redis;

class Predis {
    public $redis = "";
    
    // 单例模式的变量
    private static $_instance = null;

    public static function getInstance() {
        if(empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $this->redis    = new \Redis();
        $result         = $this->redis->connect(config('redis.host'), config('redis.port'), config('redis.timeOut'));
        if ($result === false) {
            throw new \Exception('redis connect error');
        }
    }

    // set
    public function set($key, $value, $time = 0 ) {
        if(!$key) {
            return '';
        }
        if(is_array($value)) {
            $value = json_encode($value);
        }
        if(!$time) {
            return $this->redis->set($key, $value);
        }

        return $this->redis->setex($key, $time, $value);
    }

    // get
    public function get($key) {
        if(!$key) {
            return '';
        }

        return $this->redis->get($key);
    }

    /*
    // 向集合中添加一个元素
    public function sAdd($key,$value){
        return $this->redis->sAdd($key,$value);
    }
   
    // 删除集合中的一个元素
    public function sRem($key,$value){
        return $this->redis->sRem($key,$value);
    }
    */

    // 查看集合中的所有元素
    public function sMembers($key){
        return $this->redis->sMembers($key);
    }

    // 使用魔术方法__call抽取sAdd/sRem方法(当调用类中不存在的方法时会调用__call方法)
    public function __call($name, $arguments) {
        if(count($arguments) != 2) {
            return '';
        }
        $this->redis->$name($arguments[0], $arguments[1]);
    }
}