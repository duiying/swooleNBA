<?php
/**
 * User: wyx
 * Desc: 赛况
 */

namespace app\admin\controller;
use app\common\lib\Util;
use app\common\lib\redis\Predis;

class Live
{
    public function push() {
        if(empty($_POST)) {
            return Util::show(config('code.error'), 'error');
        }
       
        $teams = [
            1 => [
                'name' => '马刺',
                'logo' => '/live/imgs/team1.png',
            ],
            4 => [
                'name' => '火箭',
                'logo' => '/live/imgs/team2.png',
            ],
        ];

        $data = [
            'type'      => intval($_POST['type']),
            'title'     => !empty($teams[$_POST['team_id']]['name']) ?$teams[$_POST['team_id']]['name'] : '直播员',
            'logo'      => !empty($teams[$_POST['team_id']]['logo']) ?$teams[$_POST['team_id']]['logo'] : '',
            'content'   => !empty($_POST['content']) ? $_POST['content'] : '',
            'image'     => !empty($_POST['image']) ? $_POST['image'] : '',
        ];
        
        $taskData = [
            'method'    => 'pushLive',
            'data'      => $data
        ];

        // 投递一个异步任务
        $_POST['http_server']->task($taskData);

        return Util::show(config('code.success'), 'ok');
    }
}
