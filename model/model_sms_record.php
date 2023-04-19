<?php
/*
 * 短信发送记录模型类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 22:16
 */

class model_sms_record extends model
{
    protected $_table = 'sms_record';

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

	public function __construct()
	{
	}

	public function __destruct()
	{
	}

}
