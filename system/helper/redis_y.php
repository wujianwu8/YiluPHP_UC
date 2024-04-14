<?php
/*
 * 连接REDIS的类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * Date: 2021.01.01
 * Time: 11:19
 */

class redis_y
{
    //存储实例化后的Rdis连接
    private static $_redis_list = [];
    //存储实例化后的redis_y对象
    private static $_redis_y = [];

    private $_obj_key = null;

    public function __construct($key)
    {
        $this->_obj_key = $key;
    }

    /**
     * 获取一个MySQL连接实例
     * @param string $db_key 在配置文件中，数据库配置使用的键名
     * @return Redis 返回已经建立连接好的对象
     */
    public static function I($redis_config_key='default', $db=null){

        $redis_config_key = empty($redis_config_key)?'default':$redis_config_key;
        if (!isset($GLOBALS['config']['redis'][$redis_config_key]) ) {
            throw new Exception('Redis配置不存在：$config[\'redis\'][\''.$redis_config_key.'\']');
        }
        if(isset(static::$_redis_list[$redis_config_key])){
            //检查连接是否断开
            if(static::$_redis_list[$redis_config_key]->ping()!=='+PONG'){
                unset(static::$_redis_list[$redis_config_key]);
            }
        }
        if ($db===null) {
            if (isset($GLOBALS['config']['redis'][$redis_config_key]['default_db'])) {
                $db = $GLOBALS['config']['redis'][$redis_config_key]['default_db'];
            }
            else {
                $db = 0;
            }
        }

        if ( !isset(static::$_redis_list[$redis_config_key]) ) {
            $redis = static::connect_redis($GLOBALS['config']['redis'][$redis_config_key]);
            $redis->select($db);
            static::$_redis_list[$redis_config_key] = $redis;
        }
        else{
            static::$_redis_list[$redis_config_key]->select($db);
            static::$_redis_list[$redis_config_key];
        }

        if ( !isset(static::$_redis_y[$redis_config_key]) ) {
            static::$_redis_y[$redis_config_key] = new self($redis_config_key);
        }
        return static::$_redis_y[$redis_config_key];
    }

    /**
     * 连接redis
     * @param $options
     * @return PDO
     * @throws Exception
     */
    public static function connect_redis($options)
    {
        $redis = new Redis();
        $redis->pconnect($options['host'], $options['port']);
        $password = $options['password'] ?? '';
        if ($password != '') {
            $redis->auth($password);
        }
        $db = $options['db'] ?? '';
        if ($db != '') {
            $redis->select($db);
        }
        return $redis;
    }

    public function __call($name, $arguments)
    {
        global $config;
        if (empty($config['disable_redis'])){
            return call_user_func_array([static::$_redis_list[$this->_obj_key], $name], $arguments);
        }
        else{
            return false;
        }
    }
}
