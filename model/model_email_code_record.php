<?php
/*
 * 邮件验证码发送记录模型类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 21:33
 */

class model_email_code_record extends model
{
    protected $_table = 'email_code_record';

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
