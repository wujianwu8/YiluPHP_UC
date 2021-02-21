<?php
/*
 * 消息队列:发邮箱验证码
 * CLI模式下运行的示例文件，需要挂在后台一直运行着
 * 执行命令：php [目录路径]queue queue_name=你加入队列时取的队列名称
 * Created by PhpStorm.
 * User: WuJianwu
 * * Date: 2021/01/23
 * Time: 20:45
 */
class send_email_code{

    /**
     * @name 开始执行队列的函数
     * @desc 开始执行队列的函数
     * @param array $msg 传递的消息数据,就是add_to_queue()函数的第二个参数$data,原样传到此处
     * [
     *  'to_email'=>收件邮箱
     *  'to_alias'=>收件人名称
     *  'subject'=>邮件标题
     *  'html_body'=>邮件内容
     * ]
     * @return boolean 返回true则完成当前消息处理,否则下次会再执行一次
     */
    public function run($msg)
    {
        if(empty($msg['to_alias']) || empty($msg['to_email']) || empty($msg['subject']) || empty($msg['html_body'])){
            //写文件日志
            write_applog('ERROR', '发邮件验证码失败，参数错误，$msg:'.json_encode($msg));
            return true;
        }

        try {
            tool_mailer::I()->to_alias = $msg['to_alias'];
            tool_mailer::I()->to_email = $msg['to_email'];
            tool_mailer::I()->subject = $msg['subject'];
            tool_mailer::I()->html_body = $msg['html_body'];
            tool_mailer::I()->auto_send();
        }
        catch (Exception $exception){
            write_applog('ERROR', $exception->getMessage().'，$msg:'.json_encode($msg).', code:'.$exception->getCode());
        }
        return true;
    }
}