<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

class Env
{
    protected $data = [];

    public function __construct()
    {
        $this->data = $_ENV;
    }

    /**
     * 读取环境变量定义文件
     * @param string    $file  环境变量定义文件
     * @return void
     */
    public function load($file)
    {
        $env = parse_ini_file($file, true);
        $this->set($env);
    }

    /**
     * 获取环境变量值
     * @param string    $name 环境变量名（支持二级 .号分割）
     * @param string    $default  默认值
     * @return mixed
     */
    public function get($name = null, $default = null)
    {
        if (is_null($name)) {
            return $this->data;
        }

        $name = strtoupper(str_replace('.', '_', $name));

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        $result = getenv('PHP_' . $name);

        if (false !== $result) {
            if ('false' === $result) {
                $result = false;
            } elseif ('true' === $result) {
                $result = true;
            }

            if (!isset($this->data[$name])) {
                $this->data[$name] = $result;
            }

            return $result;
        } else {
            return $default;
        }
    }

    /**
     * 设置环境变量值
     * @param string|array  $env   环境变量
     * @param string        $value  值
     * @return void
     */
    public function set($env, $value = null)
    {
        if (is_array($env)) {
            $env = array_change_key_case($env, CASE_UPPER);

            $this->data = array_merge($this->data, $env);

            foreach ($env as $key => $val) {
                $name = 'PHP_' . $key;
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $item = $name . '_' . strtoupper($k);
                        putenv("$item=$v");
                    }
                } else {
                    putenv("$name=$val");
                }
            }
        } else {
            $key  = strtoupper($env);
            $name = 'PHP_' . $key;
            putenv("$name=$value");
            $this->data[$key] = $value;
        }
    }
}
