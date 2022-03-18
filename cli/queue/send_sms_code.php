<?php
/*
 * 消息队列:发短信验证码
 * 需要挂在后台一直运行着处理消息队列的守护进程
 * 启动守护进程的方式：php /你的项目目录/queue "queue_name=你加入队列时取的队列名称"
 * 如果要让队列在系统后台默默运行，在命令的最后面加一个与号就行了，这样执行：php /你的项目目录/queue "queue_name=你加入队列时取的队列名称" &
 * Created by PhpStorm.
 * User: WuJianwu
 * * Date: 2021/01/23
 * Time: 20:45
 */
class send_sms_code {
    /**
     * @name 开始执行队列的函数
     * @desc 开始执行队列的函数
     * @param array $msg 传递的消息数据,就是add_to_queue()函数的第二个参数$data,原样传到此处
     * [
     *  'area_code'=>手机区号
     *  'mobile'=>手机号码
     *  'message'=>短信内容
     * ]
     * @return boolean 返回true则完成当前消息处理,否则下次会再执行一次
     */
    public function run($msg)
    {
        if(empty($msg['area_code']) || empty($msg['mobile']) || empty($msg['message']) || empty($msg['template_code']) || empty($msg['sign_name']) || empty($msg['template_param'])){
            //写文件日志
            write_applog('ERROR', '发短信验证码失败,参数错误,,$msg:'.json_encode($msg));
            return true;
        }
        tool_sms::I()->send_verify_code($msg['area_code'], $msg['mobile'], $msg['message'], $msg['template_code'], $msg['sign_name'], $msg['template_param']);
        return true;
    }
}