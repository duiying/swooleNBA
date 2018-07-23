<?php
/**
 * User: wyx
 * Desc: 短信发送
 */

namespace app\index\controller;
use app\common\lib\ali\Sms;
use app\common\lib\Util;
use app\common\lib\Redis;

class Send
{
    public function index() {
        $phoneNum = intval($_GET['phone_num']);

        // 检验手机号是否为空
        if (empty($phoneNum)) {
            return Util::show(config('code.error'), 'error');
        }

        // 生成一个4位随机数作为验证码
        $code = rand(1000, 9999);

        $taskData = [
            'method'    => 'sendSms',
            'data'      => [
                'phone'     => $phoneNum,
                'code'      => $code,
            ]
        ];

        // 投递一个异步任务
        $_POST['http_server']->task($taskData);
        
        return Util::show(config('code.success'), 'ok');
    }
}
