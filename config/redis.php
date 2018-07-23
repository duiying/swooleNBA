<?php
/**
 * User: wyx
 * Desc: redis配置
 */

return [
    'host' 				=> '127.0.0.1',
    'port' 				=> 6379,
    'out_time' 			=> 120,
    'timeOut' 			=> 5, 					// redis连接超时时间
    'live_user' 		=> 'live_user'			// redis存储用户连接时使用的key
];