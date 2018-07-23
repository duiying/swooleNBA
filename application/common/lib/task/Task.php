<?php
/**
 * User: wyx
 * Desc: 所有的异步任务
 */

namespace app\common\lib\task;
use app\common\lib\ali\Sms;
use app\common\lib\redis\Predis;
use app\common\lib\Redis;

class Task {

    // 异步发送验证码
    public function sendSms($data, $serv) {
        try {
            $response = Sms::sendSms($data['phone'], $data['code']);
        }catch (\Exception $e) {
            // todo
            return false;
        }

        // 如果发送成功,把验证码存储到redis
        if ($response->Code === "OK") {
            Predis::getInstance()->set(Redis::smsKey($data['phone']), $data['code'], config('redis.out_time'));
        } else {
            return false;
        }
        return true;
    }

    // 向客户端异步推送赛况数据
    public function pushLive($data, $serv) {
        $clients = Predis::getInstance()->sMembers(config("redis.live_user"));

        foreach($clients as $fd) {
            $serv->push($fd, json_encode($data));
        }
    }
}