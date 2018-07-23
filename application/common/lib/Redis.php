<?php
/**
 * User: wyx
 * Desc: redis类库
 */

namespace app\common\lib;

class Redis 
{
    // 验证码key的前缀
    public static $pre = "sms_";
    
    // 用户key的前缀
    public static $userpre = "user_";

    // 验证码key
    public static function smsKey($phone) {
        return self::$pre.$phone;
    }

    // 用户key
    public static function userkey($phone) {
        return self::$userpre.$phone;
    }
}