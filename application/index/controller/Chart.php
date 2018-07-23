<?php
/**
 * User: wyx
 * Desc: 向客户端推送数据(聊天室)
 */

namespace app\index\controller;
use app\common\lib\Util;

class Chart
{
    public function index()
    {
        // 登录场景判断
        // todo
        
        // 检查数据
        if (empty($_POST['game_id'])) {
            return Util::show(config('code.error'), 'error');
        }
        if (empty($_POST['content'])) {
            return Util::show(config('code.error'), 'error');
        }

        // 模拟用户而非真实用户
        $data = [
            'user' => "用户".rand(0, 2000),
            'content' => $_POST['content'],
        ];
     
        // $connections => TCP连接迭代器
        // 连接迭代器依赖pcre库(不是PHP的pcre扩展),未安装pcre库无法使用此功能
        // 获取8812端口当前用户的连接
        // 遍历的元素为单个连接的fd
        foreach($_POST['http_server']->ports[1]->connections as $fd) {
            // 向websocket客户端连接推送数据
            // $fd 客户端连接的ID
            $_POST['http_server']->push($fd, json_encode($data));
        }

        return Util::show(config('code.success'), 'ok', $data);
    }


}
