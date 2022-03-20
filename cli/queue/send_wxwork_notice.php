<?php
/*
 * 发送企业微信消息
 * 需要在后台一直运行着处理消息队列的守护进程
 * 启动守护进程的方式：php /你的项目目录/queue "queue_name=你加入队列时取的队列名称"
 * 如果要让队列在系统后台默默运行，在命令的最后面加一个与号就行了，这样执行：php /你的项目目录/queue "queue_name=你加入队列时取的队列名称" &
 * Created by PhpStorm.
 * User: WuJianwu
 * * Date: 2022/03/20
 * Time: 20:35
 */
class send_wxwork_notice {
    /**
     * @name 开始执行队列的函数
     * @desc 开始执行队列的函数
     * @param array $msg 传递的消息数据,就是add_to_queue()函数的第二个参数$data,原样传到此处
     * [
     *  'content' =>文本内容，最长不超过2048个字节，必须是utf8编码
     *  'robot_key' =>机器人编码，不同的机器人的编码不一样，可按业务创建不同的机器人
     *  'msgtype' =>消息类型 默认是文本，支持文本（text）、markdown（markdown）、图片（image）、图文（news）四种消息类型。文本不可发链接，可用markdown
     *  'mentioned_list' =>userid的列表，提醒群中的指定成员(@某个成员)，@all表示提醒所有人，如果开发者获取不到userid，可以使用mentioned_mobile_list
     *  'mentioned_mobile_list' =>手机号列表，提醒手机号对应的群成员(@某个成员)，@all表示提醒所有人
     * ]
     * @return boolean 返回true则完成当前消息处理,否则下次会再执行一次
     */
    public function run($msg)
    {
        if (empty($msg['content']) || empty($msg['robot_key'])){
            //写文件日志
            write_applog('ERROR', '发短企业微信消息失败,参数错误,,$msg:'.json_encode($msg));
            return true;
        }
        if (empty($msg['msgtype'])){
            $msg['msgtype'] = 'text';
        }
        if (empty($msg['mentioned_list'])){
            $msg['mentioned_list'] = [];
        }
        if (empty($msg['mentioned_mobile_list'])){
            $msg['mentioned_mobile_list'] = [];
        }

        tool_operate::I()->send_work_notice($msg['content'], $msg['robot_key'], $msg['msgtype'], $msg['mentioned_list'], $msg['mentioned_mobile_list']);
        return true;
    }
}