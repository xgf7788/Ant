<?php
//DB工厂类
namespace Ant;

class Db{
    //  数据库连接实例
    private static $instance = [];
    // 查询次数
    public static $queryTimes = 0;
    // 执行次数
    public static $executeTimes = 0;


    /**
     * 数据库初始化 并取得数据库类实例
     * @static
     * @access public
     * @param mixed         $config 连接配置
     * @param bool|string   $name 连接标识 true 强制重新连接
     * @return \think\db\Connection
     * @throws Exception
     */
    public function init($config = [], $name = false){
        if (false === $name) {
            $name = md5(serialize($config));
        }
        if (true === $name || !isset(self::$instance[$name])) {
            // 解析连接参数 支持数组和字符串
            $options = self::parseConfig($config);
            if (empty($options['type'])) {
                throw new \InvalidArgumentException('Underfined db type');
            }
            $class = false !== strpos($options['type'], '\\') ? $options['type'] : '\\Ant\\Database\\Connector\\' . ucwords($options['type']);
            // 记录初始化信息
//            App::$debug && Log::record('[ DB ] INIT ' . $options['type'] . ':' . var_export($options, true), 'info');

            if (true === $name) {
                //返回一个新对象
                return new $class($options);
            } else {
                self::$instance[$name] = new $class($options);
            }
        }
        return self::$instance[$name];
    }


    /**
     * 数据库连接参数解析
     * @static
     * @access private
     * @param mixed $config
     * @return array
     */
    private static function parseConfig($config)
    {
        if (empty($config)) {
            $config = Config::get('database');
        } elseif (is_string($config) && false === strpos($config, '/')) {
            // 支持读取配置参数
            $config = Config::get($config);
        }
        if (is_string($config)) {
            return self::parseDsn($config);
        } else {
            return $config;
        }
    }

    /**
     * DSN解析
     * 格式： mysql://username:passwd@localhost:3306/DbName?param1=val1&param2=val2#utf8
     * @static
     * @access private
     * @param string $dsnStr
     * @return array
     */
    private static function parseDsn($dsnStr)
    {
        $info = parse_url($dsnStr);
        if (!$info) {
            return [];
        }
        $dsn = [
            'type'     => $info['scheme'],
            'username' => isset($info['user']) ? $info['user'] : '',
            'password' => isset($info['pass']) ? $info['pass'] : '',
            'hostname' => isset($info['host']) ? $info['host'] : '',
            'hostport' => isset($info['port']) ? $info['port'] : '',
            'database' => !empty($info['path']) ? ltrim($info['path'], '/') : '',
            'charset'  => isset($info['fragment']) ? $info['fragment'] : 'utf8',
        ];

        if (isset($info['query'])) {
            parse_str($info['query'], $dsn['params']);
        } else {
            $dsn['params'] = [];
        }
        return $dsn;
    }

    // 调用驱动类的方法
    public static function __callStatic($method, $params)
    {
        // 自动初始化数据库
        return call_user_func_array([self::init(), $method], $params);
    }
}