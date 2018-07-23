<?php
/**
 * User: wyx
 * Desc: 登录
 */

namespace app\index\controller;
use app\common\lib\Util;
use app\common\lib\Redis;
use app\common\lib\redis\Predis;

class Login
{
    public function index() {
        $phoneNum   = intval($_POST['phone_num']);  // 手机号
        $code       = intval($_POST['code']);       // 验证码

        // 检查手机号和验证码是否为空
        if (empty($phoneNum) || empty($code)) {
            return Util::show(config('code.error'), 'phone or code is error');
        }

        // 从redis中取出验证码
        try {
            $redisCode = Predis::getInstance()->get(Redis::smsKey($phoneNum));
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        // 判断 从redis中取出的验证码 和 用户提交的验证码 是否相同
        if($redisCode == $code) {
            // 将用户信息写入redis
            $data = [
                'user' => $phoneNum,
                'srcKey' => md5(Redis::userkey($phoneNum)),
                'time' => time(),
                'isLogin' => true,
            ];
            Predis::getInstance()->set(Redis::userkey($phoneNum), $data);
            return Util::show(config('code.success'), 'ok', $data);
        } else {
            return Util::show(config('code.error'), 'login error');
        }
    }
}
