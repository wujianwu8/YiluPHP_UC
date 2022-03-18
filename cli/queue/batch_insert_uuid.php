<?php
/*
 * 消息队列:添加一批UUID
 * 需要挂在后台一直运行着处理消息队列的守护进程
 * 启动守护进程的方式：php /你的项目目录/queue "queue_name=你加入队列时取的队列名称"
 * 如果要让队列在系统后台默默运行，在命令的最后面加一个与号就行了，这样执行：php /你的项目目录/queue "queue_name=你加入队列时取的队列名称" &
 * Created by PhpStorm.
 * User: WuJianwu
 * * Date: 2022/03/18
 * Time: 20:16
 */
class batch_insert_uuid {
    /**
     * @name 开始执行队列的函数
     * @desc 开始执行队列的函数
     * @param array $msg 传递的消息数据,就是add_to_queue()函数的第二个参数$data,原样传到此处
     * [
     *  'count'=>需要添加的UUID数量
     * ]
     * @return boolean 返回true则完成当前消息处理,否则下次会再执行一次
     */
    public function run($msg)
    {
        if (!isset($msg['count']) || empty($msg['count'])){
            //写文件日志
            write_applog('ERROR', '添加一批UUID时失败，参数错误，$msg:'.json_encode($msg));
            return true;
        }
        $count = intval($msg['count']);
        if ($count<1){
            //写文件日志
            write_applog('ERROR', '添加一批UUID时失败，参数错误，$msg:'.json_encode($msg));
            return true;
        }
        $disuse_old = !isset($msg['disuse_old'])||!empty($msg['disuse_old'])?true:false;
        logic_uuid::I()->batch_insert_uuid($count, $disuse_old);
        return true;
    }
}