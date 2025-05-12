<?php
/**
 * 验证类异常，可以返回给用户看的
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * Date: 2021/01/29
 * Time: 23:38
 **/

class validate_exception extends Exception
{
    public $data=null;

    /**
     * 异常构造函数
     * @param string $message [optional] 抛出的异常消息内容
     * @param int $code [optional] 异常代码
     * @param Throwable $previous [optional] 异常链中的前一个异常
     */
    public function __construct($message='', $code = 0, $data=null) {
        $this->data = $data;
        parent::__construct($message, $code);
    }

    public function getData() {
        return $this->data;
    }
}
