<?php
/*
 * 所有类的基类
 * 实现存储类的单例，及实例化
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * Date: 2021/01/22
 * Time: 23:38
 */

class base_class
{
    //存储所有类的单例
    protected static $instances = [];

    /**
     * 获取单例
     */
    public static function I(){
        $class_name = get_called_class();
        if (empty($class_name) && empty(self::$instances[$class_name])){
            return self::$instances[$class_name] = new static();
        }
        if (empty(self::$instances[$class_name])){
            return self::$instances[$class_name] = new $class_name();
        }
        return self::$instances[$class_name];
    }

}
