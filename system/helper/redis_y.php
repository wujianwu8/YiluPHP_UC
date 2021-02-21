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
    //存储实例化后的连接
    private static $_redis_list = [];

    /**
     * 获取一个MySQL连接实例
     * @param string $db_key 在配置文件中，数据库配置使用的键名
     * @return object 返回已经建立连接好的对象
     */
    public static function I($redis_config_key='default', $db=0){

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
        if ( !isset(static::$_redis_list[$redis_config_key]) ) {
            $redis = static::connect_redis($GLOBALS['config']['redis'][$redis_config_key]);
            $redis->select($db);
            return static::$_redis_list[$redis_config_key] = $redis;
        }
        else{
            static::$_redis_list[$redis_config_key]->select($db);
            return static::$_redis_list[$redis_config_key];
        }
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
        return $redis;
    }

}
