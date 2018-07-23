<?php
/**
 * User: wyx
 * Desc: 工具类
 */

namespace app\common\lib;
class Util {

    // API输出格式
    public static function show($status, $message = '', $data = []) {
        $result = [
            'status'    => $status,
            'message'   => $message,
            'data'      => $data,
        ];

        echo json_encode($result);
    }
}