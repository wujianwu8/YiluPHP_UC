<?php
/*
 * 菜单模型类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 21:44
 */

class model_menus extends model
{
    protected $_table = 'menus';

    protected static $instance = null;

    /**
     * 获取单例
     */
    public static function I(){
        if (empty(self::$instance)){
            return self::$instance = new static();
        }
        return self::$instance;
    }


}
