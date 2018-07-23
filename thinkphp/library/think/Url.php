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

class Url
{
    // 生成URL地址的root
    protected $root;
    protected $bindCheck;

    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;

        if (is_file($app->getRuntimePath() . 'route.php')) {
            // 读取路由映射文件
            $app['route']->setName(include $app->getRuntimePath() . 'route.php');
        }
    }

    /**
     * URL生成 支持路由反射
     * @param string            $url 路由地址
     * @param string|array      $vars 参数（支持数组和字符串）a=val&b=val2... ['a'=>'val1', 'b'=>'val2']
     * @param string|bool       $suffix 伪静态后缀，默认为true表示获取配置值
     * @param boolean|string    $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    public function build($url = '', $vars = '', $suffix = true, $domain = false)
    {
        // 解析URL
        if (0 === strpos($url, '[') && $pos = strpos($url, ']')) {
            // [name] 表示使用路由命名标识生成URL
            $name = substr($url, 1, $pos - 1);
            $url  = 'name' . substr($url, $pos + 1);
        }

        if (false === strpos($url, '://') && 0 !== strpos($url, '/')) {
            $info = parse_url($url);
            $url  = !empty($info['path']) ? $info['path'] : '';

            if (isset($info['fragment'])) {
                // 解析锚点
                $anchor = $info['fragment'];

                if (false !== strpos($anchor, '?')) {
                    // 解析参数
                    list($anchor, $info['query']) = explode('?', $anchor, 2);
                }

                if (false !== strpos($anchor, '@')) {
                    // 解析域名
                    list($anchor, $domain) = explode('@', $anchor, 2);
                }
            } elseif (strpos($url, '@') && false === strpos($url, '\\')) {
                // 解析域名
                list($url, $domain) = explode('@', $url, 2);
            }
        }

        // 解析参数
        if (is_string($vars)) {
            // aaa=1&bbb=2 转换成数组
            parse_str($vars, $vars);
        }

        if ($url) {
            $rule = $this->app['route']->getName(isset($name) ? $name : $url . (isset($info['query']) ? '?' . $info['query'] : ''));

            if (is_null($rule) && isset($info['query'])) {
                $rule = $this->app['route']->getName($url);
                // 解析地址里面参数 合并到vars
                parse_str($info['query'], $params);
                $vars = array_merge($params, $vars);
                unset($info['query']);
            }
        }

        if (!empty($rule) && $match = $this->getRuleUrl($rule, $vars)) {
            // 匹配路由命名标识
            $url = $match[0];
            // 替换可选分隔符
            $url = preg_replace(['/(\W)\?$/', '/(\W)\?/'], ['', '\1'], $url);

            if (!empty($match[1])) {
                $host = $this->app['config']->get('app_host') ?: $this->app['request']->host();
                if ($domain || $match[1] != $host) {
                    $domain = $match[1];
                }
            }

            if (!is_null($match[2])) {
                $suffix = $match[2];
            }
        } elseif (!empty($rule) && isset($name)) {
            throw new \InvalidArgumentException('route name not exists:' . $name);
        } else {
            // 检查别名路由
            $alias      = $this->app['route']->getAlias();
            $matchAlias = false;

            if ($alias) {
                // 别名路由解析
                foreach ($alias as $key => $val) {
                    if (is_array($val)) {
                        $val = $val[0];
                    }

                    if (0 === strpos($url, $val)) {
                        $url        = $key . substr($url, strlen($val));
                        $matchAlias = true;
                        break;
                    }
                }
            }

            if (!$matchAlias) {
                // 路由标识不存在 直接解析
                $url = $this->parseUrl($url, $domain);
            }

            if (isset($info['query'])) {
                // 解析地址里面参数 合并到vars
                parse_str($info['query'], $params);
                $vars = array_merge($params, $vars);
            }
        }

        // 检测URL绑定
        if (!$this->bindCheck) {
            $bind = $this->app['route']->getBind();

            if (0 === strpos($url, $bind)) {
                $url = substr($url, strlen($bind) + 1);
            }

        }
        // 还原URL分隔符
        $depr = $this->app['config']->get('pathinfo_depr');
        $url  = str_replace('/', $depr, $url);

        // URL后缀
        $suffix = in_array($url, ['/', '']) ? '' : $this->parseSuffix($suffix);

        // 锚点
        $anchor = !empty($anchor) ? '#' . $anchor : '';

        // 参数组装
        if (!empty($vars)) {
            // 添加参数
            if ($this->app['config']->get('url_common_param')) {
                $vars = urldecode(http_build_query($vars));
                $url .= $suffix . '?' . $vars . $anchor;
            } else {
                $paramType = $this->app['config']->get('url_param_type');

                foreach ($vars as $var => $val) {
                    if ('' !== trim($val)) {
                        if ($paramType) {
                            $url .= $depr . urlencode($val);
                        } else {
                            $url .= $depr . $var . $depr . urlencode($val);
                        }
                    }
                }

                $url .= $suffix . $anchor;
            }
        } else {
            $url .= $suffix . $anchor;
        }

        // 检测域名
        $domain = $this->parseDomain($url, $domain);

        // URL组装
        $url = $domain . rtrim($this->root ?: $this->app['request']->root(), '/') . '/' . ltrim($url, '/');

        $this->bindCheck = false;

        return $url;
    }

    // 直接解析URL地址
    protected function parseUrl($url, &$domain)
    {
        $request = $this->app['request'];

        if (0 === strpos($url, '/')) {
            // 直接作为路由地址解析
            $url = substr($url, 1);
        } elseif (false !== strpos($url, '\\')) {
            // 解析到类
            $url = ltrim(str_replace('\\', '/', $url), '/');
        } elseif (0 === strpos($url, '@')) {
            // 解析到控制器
            $url = substr($url, 1);
        } else {
            // 解析到 模块/控制器/操作
            $module = $request->module();
            $module = $module ? $module . '/' : '';

            $controller = Loader::parseName($request->controller());

            if ('' == $url) {
                // 空字符串输出当前的 模块/控制器/操作
                $url = $module . $controller . '/' . $request->action();
            } else {
                $path       = explode('/', $url);
                $action     = $this->app['config']->get('url_convert') ? strtolower(array_pop($path)) : array_pop($path);
                $controller = empty($path) ? $controller : ($this->app['config']->get('url_convert') ? Loader::parseName(array_pop($path)) : array_pop($path));
                $module     = empty($path) ? $module : array_pop($path) . '/';
                $url        = $module . $controller . '/' . $action;
            }
        }

        return $url;
    }

    // 检测域名
    protected function parseDomain(&$url, $domain)
    {
        if (!$domain) {
            return '';
        }

        if (true === $domain) {

            // 自动判断域名
            $domain     = $this->app['config']->get('app_host') ?: $this->app['request']->host();
            $rootDomain = $this->app['config']->get('url_domain_root');

            $domains = $this->app['route']->getDomains();

            if ($domains) {
                $route_domain = array_keys($domains);
                foreach ($route_domain as $domain_prefix) {
                    if (0 === strpos($domain_prefix, '*.') && strpos($domain, ltrim($domain_prefix, '*.')) !== false) {
                        foreach ($domains as $key => $rule) {
                            $rule = is_array($rule) ? $rule[0] : $rule;
                            if (is_string($rule) && false === strpos($key, '*') && 0 === strpos($url, $rule)) {
                                $url    = ltrim($url, $rule);
                                $domain = $key;

                                // 生成对应子域名
                                if (!empty($rootDomain)) {
                                    $domain .= $rootDomain;
                                }
                                break;
                            } elseif (false !== strpos($key, '*')) {
                                if (!empty($rootDomain)) {
                                    $domain .= $rootDomain;
                                }

                                break;
                            }
                        }
                    }
                }
            }
        }

        if (false !== strpos($domain, '://')) {
            $scheme = '';
        } else {
            $scheme = $this->app['request']->isSsl() || $this->app['config']->get('is_https') ? 'https://' : 'http://';

        }

        return $scheme . $domain;
    }

    // 解析URL后缀
    protected function parseSuffix($suffix)
    {
        if ($suffix) {
            $suffix = true === $suffix ? $this->app['config']->get('url_html_suffix') : $suffix;

            if ($pos = strpos($suffix, '|')) {
                $suffix = substr($suffix, 0, $pos);
            }
        }

        return (empty($suffix) || 0 === strpos($suffix, '.')) ? $suffix : '.' . $suffix;
    }

    // 匹配路由地址
    public function getRuleUrl($rule, &$vars = [])
    {
        foreach ($rule as $item) {
            list($url, $pattern, $domain, $suffix) = $item;
            if (empty($pattern)) {
                return [$url, $domain, $suffix];
            }

            foreach ($pattern as $key => $val) {
                if (isset($vars[$key])) {
                    $url = str_replace(['[:' . $key . ']', '[:' . $key . '$]', '<' . $key . '?>', ':' . $key . '', ':' . $key . '$', '<' . $key . '>'], urlencode($vars[$key]), $url);
                    unset($vars[$key]);
                    $result = [$url, $domain, $suffix];
                } elseif (2 == $val) {
                    $url    = str_replace(['/[:' . $key . ']', '/[:' . $key . '$]', '[:' . $key . ']', '[:' . $key . '$]', '<' . $key . '?>'], '', $url);
                    $result = [$url, $domain, $suffix];
                } else {
                    break;
                }
            }

            if (isset($result)) {
                return $result;
            }
        }

        return false;
    }

    // 指定当前生成URL地址的root
    public function root($root)
    {
        $this->root = $root;
        $this->app['request']->root($root);
    }
}
