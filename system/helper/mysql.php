<?php
/*
 * 连接MySQL的类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * Date: 2021.01.01
 * Time: 11:19
 */

class mysql
{
    //存储实例化后的连接
    private static $mysql_list = [];

    /**
     * 获取一个MySQL连接实例
     * @param string $db_key 在配置文件中，数据库配置使用的键名
     * @return object 返回已经建立连接好的对象
     */
    public static function I($db_key='default'){
        $db_key = empty($db_key)?'default':$db_key;
        if (!isset($GLOBALS['config']['mysql'][$db_key]) ) {
            throw new Exception('MySQL数据库配置不存在：$config[\'mysql\'][\''.$db_key.'\']');
        }
        if(isset(static::$mysql_list[$db_key])){
            //检查连接是否断开
            if(!static::_pdo_ping(static::$mysql_list[$db_key])){
                unset(static::$mysql_list[$db_key]);
            }
        }
        if ( !isset(static::$mysql_list[$db_key]) ) {
            return static::$mysql_list[$db_key] = static::connect_mysql($GLOBALS['config']['mysql'][$db_key]);
        }
        return static::$mysql_list[$db_key];
    }

    /**
     * 检查连接是否可用
     * @param Link $dbconn 数据库连接
     * @return Boolean
     */
    public static function _pdo_ping($dbconn){
        try{
            $dbconn->getAttribute(PDO::ATTR_SERVER_INFO);
        } catch (PDOException $e) {
            if(strpos($e->getMessage(), 'MySQL server has gone away')!==false){
                return false;
            }
        }
        return true;
    }

    /**
     * 连接数据库
     * @param $options
     * @return PDO
     * @throws Exception
     */
    public static function connect_mysql($options)
    {
        try {
            $pdo = new PDO(
                $options['dsn'],
                $options['username'],
                $options['password'],
                $options['option']
            );
            // 设置 PDO 错误模式为异常，用于抛出异常
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //是否在提取的时候将数值转换为字符串
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            if (isset($options['charset'])) {
                $pdo->exec('SET NAMES "'.$options['charset'].'"');
            }
            return $pdo;
        }
        catch (PDOException $e) {
            write_applog('ERROR', '连接数据库失败，错误信息：'.$e->getMessage()
                .'，连接参数：'.json_encode($options, JSON_UNESCAPED_UNICODE));
            throw new Exception($e->getMessage());
        }
    }

}
