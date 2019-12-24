<?php
/*
 * 消息队列的主类
 * 用户写的消息队列业务处理类需要继承queue类
 * 使用redis列表实现
 * Created by PhpStorm.
 * User: WuJianwu
 * Date: 19/12/24
 * Time: 18:45
 */

class queue{
    public $interval = 0; //处理完一个消息之后睡眠多久再处理下一个,单位:毫秒
    public $queue_name = null; //队列名称,参与redis的键名
    public $backups_num = 100000; //已经处理过的消息会进行备份,这是最多可备份的消息数量,如果超过这个数量则会把老的消息删除掉
    private $redis_key = null;

    function __construct($queue_name, $action='')
    {
        if(!$queue_name){
            exit("请给你的消息队列取一个名称吧\r\n");
        }
        $this->queue_name = $queue_name;

        //如果是同步模式,则不运行后台的程序
        if(!empty($GLOBALS['config']['queue_mode']) && $GLOBALS['config']['queue_mode']=='sync'){
            write_applog('NOTICE', '当前配置的消息列队是同步模式，不在后台运行消息队列程序');
            return;
        }

        $this->redis_key = 'yiluphp_queue'.$this->queue_name;
        global $app;
        if(!$app->redis()->hexists('yiluphp_queue_list_for_manage', $this->queue_name)){
            echo "消息队列的管理列表中不存在此队列：".$this->queue_name."\r\n";
            write_applog('ERROR', '消息队列的管理列表中不存在此队列：'.$this->queue_name);
            return;
        }
        //如果有参数,则执行参数的命令:stop pause delete
        if($action){
            switch($action){
                //停止当前队列,正在执行的消息将继续执行完毕
                case 'stop':
                    if($this->stop()){
                        exit('成功停止队列:'.$this->queue_name.",正在执行的消息将继续执行完毕\r\n");
                    }
                    exit('停止队列:'.$this->queue_name."失败\r\n");
                //暂停当前队列,不会有新的消息加入执行,正在执行的消息将继续执行完毕
                case 'pause':
                    if($this->pause()){
                        exit('成功暂停队列:'.$this->queue_name.",正在执行的消息将继续执行完毕\r\n");
                    }
                    exit('暂停队列:'.$this->queue_name."失败\r\n");
                //删除当前队列,不会有新的消息加入执行,正在执行的消息将继续执行完毕
                case 'delete':
                    if($this->delete()){
                        exit('成功删除队列:'.$this->queue_name.",正在执行的消息将继续执行完毕\r\n");
                    }
                    exit('删除队列:'.$this->queue_name."失败\r\n");
                //恢复当前队列,继续处理消息
                case 'start':
                    if($this->start()){
                        echo('成功恢复队列:'.$this->queue_name.",继续处理消息\r\n");
                    }
                    else {
                        exit('恢复队列:' . $this->queue_name . "失败\r\n");
                    }
                default:
                    break;
            }
        }
        $this->start();
        //记录连续多少次没有消息,如果连续多次没取到消息,则适当延长读取消息的频繁
        $empty_times = 0;
        while($this->queue_name){
            try {
                $queue_info = $app->redis()->hget('yiluphp_queue_list_for_manage', $this->queue_name);
                $queue_info && $queue_info=json_decode($queue_info, true);
                if($queue_info && isset($queue_info['status'])){
                    if($queue_info['status']=='stop') {
                        exit("Stoped.\r\n");
                    }
                    else if($queue_info['status']=='pause') {
                        $this->interval = 3000;
                        continue;
                    }
                    else if($queue_info['status']=='delete') {
                        exit("Deleted.\r\n");
                    }
                }
                //Blpop命令移出并获取列表的第一个元素， 如果列表没有元素会阻塞列表直到等待超时或发现可弹出元素为止。超时返回空数组
//            $first_msg = $app->redis()->blpop($this->redis_key, 60); //60秒超时
                //Blpop命令移出并获取列表的第一个元素， 如果列表没有元素会阻塞列表直到等待超时或发现可弹出元素为止。超时返回false
                $first_msg = $app->redis()->brpoplpush($this->redis_key, 'doing' . $this->redis_key, 10); //10秒超时
                if ($first_msg) {
                    $msg = json_decode($first_msg, true);
                    //$msg中包含创建消息的时间ctime,消息内容data(由用户添加消息时加入)
                    if ($msg) {
                        if (!isset($msg['delay']) || (isset($msg['delay']) && intval($msg['delay'])+$msg['ctime']<time()) ) {
                            if(empty($msg['class_name'])){
                                //写错误日志
                                write_applog('ERROR', '队列消息数据不完整,缺少class_name值:' . $first_msg);
                                if (!$app->redis()->lrem('doing' . $this->redis_key, $first_msg, 1)) {
                                    //如果删除失败,则写错误日志
                                    write_applog('ERROR', '从处理中的列表中删除元素失败:' . $first_msg);
                                }
                                continue;
                            }
                            $file = $GLOBALS['project_root'].'cli/queue/'.$msg['class_name'].'.php';
                            $res = false;
                            if(!file_exists($file)){
                                write_applog('ERROR', '未找到消息列表的实现文件:'.$file."\r\n");
                            }
                            else {
                                include_once $file;
                                if (!class_exists($msg['class_name'])) {
                                    write_applog('ERROR', '在文件' . $file . '中，未找到消息列表的实现类:class ' . $msg['class_name'] . "{}\r\n");
                                }
                                else{
                                    $queue = $msg['class_name'];
                                    $queue = new $queue();
                                    $res = $queue->run($msg['data']);
                                }
                            }

                            if ($res) {
                                $this->interval=0;
                                $empty_times = 0;
                                //加入处理成功的时间
                                $msg['dtime'] = time();
                                //把已经处理过的元素存入另一个备份的列表
                                $count = $app->redis()->lpush('bak' . $this->redis_key, json_encode($msg));
                                //如果备份的数量超限,则删除旧的数据
                                if ($count > $this->backups_num) {
                                    $app->redis()->ltrim('bak' . $this->redis_key, 0, $this->backups_num - 1);
                                }
                            } else {
                                //如果处理失败,则把这个消息移回到列表的最后面
                                //加入已经重试过处理的次数
                                $msg['retry'] = isset($msg['retry']) ? $msg['retry'] + 1 : 1;
                                //为节省存储空间最多记到1千万
                                $msg['retry'] > 9999999 && $msg['retry'] = 10000000;
                                //加入第一次重试的时间
                                !isset($msg['retrytime']) && $msg['retrytime'] = time();
                                //前10次失败每6秒重试一次
                                if($msg['retry']<10){
                                    $msg['delay'] = time()+6;
                                }
                                else{
                                    //重试10次失败后,每1分钟重试一次
                                    $msg['delay'] = time()+60;
                                }
                                $count = $app->redis()->rpush($this->redis_key, json_encode($msg));
                            }
                            if ($count) {
                                //从处理中的列表中删除该元素
                                if (!$app->redis()->lrem('doing' . $this->redis_key, $first_msg, 1)) {
                                    //如果删除失败,则写错误日志
                                    write_applog('ERROR', '从处理中的列表中删除元素失败:' . $first_msg);
                                }
                            }
                            unset($count);
                        }
                        else if (isset($msg['delay'])){
                            //需要延迟的消息,且没到时间的继续放回到队列的最后面
                            $app->redis()->rpush($this->redis_key, $first_msg);
                        }
                        else{
                            //写错误日志
                            write_applog('ERROR', '队列消息异常:' . $first_msg);
                            if (!$app->redis()->lrem('doing' . $this->redis_key, $first_msg, 1)) {
                                //如果删除失败,则写错误日志
                                write_applog('ERROR', '从处理中的列表中删除元素失败:' . $first_msg);
                            }
                        }
                    }
                    else{
                        //写错误日志
                        write_applog('ERROR', '解析队列消息失败:' . $first_msg);
                        if (!$app->redis()->lrem('doing' . $this->redis_key, $first_msg, 1)) {
                            //如果删除失败,则写错误日志
                            write_applog('ERROR', '从处理中的列表中删除元素失败:' . $first_msg);
                        }
                    }
                }
                else{
                    $empty_times ++;
                }
                if($empty_times>2){
                    $this->interval=1000;
                }
                unset($msg, $first_msg);
            }
            catch (RedisException $e) {
                //写文件日志
                write_applog('ERROR', '队列出错了（可能是redis原因）');
            }
            usleep($this->interval);
        }
    }

    /**
     * 消息列表的业务处理入口方法
     * 用户的消息列表需要重写这个方法
     * 一定要返回一个boolean值,如果返回true,则此消息以后不再处理,如果返回false或者不返回,则以后还会再处理这个消息
     **/
    public function run($msg){
        //这里是业务处理代码
    }

    /**
     * 停止当前消息列表,正在执行的消息将继续执行完毕
     **/
    public function stop(){
        return $this->_get_doing_list('stop');
    }

    /**
     * 暂停当前队列,不会有新的消息加入执行,正在执行的消息将继续执行完毕
     **/
    public function pause(){
        return $this->_get_doing_list('pause');
    }

    /**
     * 删除当前队列,不会有新的消息加入执行,正在执行的消息将继续执行完毕
     **/
    public function delete(){
        return $this->_get_doing_list('delete');
    }

    /**
     * 恢复当前队列,继续处理消息
     **/
    public function start(){
        return $this->_get_doing_list('running');
    }

    /**
     * 设置当前消息列表的状态:stop pause delete running
     **/
    private function _get_doing_list($status){
        if(!in_array($status, ['stop','pause', 'delete', 'running'] )){
            return false;
        }
        $res = $GLOBALS['app']->redis()->hget('yiluphp_queue_list_for_manage', $this->queue_name);
        $res && $res=json_decode($res, true);
        if($res){
            $res['status'] = $status;
            $count = $GLOBALS['app']->redis()->hset('yiluphp_queue_list_for_manage', $this->queue_name, json_encode($res) );
            if(in_array($count, [0,1])){
                return true;
            }
            else{
                return false;
            }
        }
        return true;
    }

    /**
     * 获取指定名称的消息队列中正在执行的消息列表
     * $queue_name
     * $page
     * $page_size
     **/
    static public function get_doing_list($queue_name, $page=1, $page_size=10){
        $start = ($page-1)*$page_size;
        $data = $GLOBALS['app']->redis()->lrange('doing_yiluphp_queue'.$queue_name, $start, $start+$page_size);
        $data = !$data ? []:$data;
        return $data;
    }

    /**
     * 获取指定名称的消息队列中已经成功执行的消息列表
     * $queue_name
     * $page
     * $page_size
     **/
    static public function get_dealt_list($queue_name, $page=1, $page_size=10){
        $start = ($page-1)*$page_size;
        $start = ($page-1)*$page_size;
        $data = $GLOBALS['app']->redis()->lrange('backup_yiluphp_queue'.$queue_name, $start, $start+$page_size);
        $data = !$data ? []:$data;
        return $data;
    }

    /**
     * 将卡在正在执行列表中的消息移回到消息队列中
     * $queue_name
     * $time_ago 单位秒，多久以前加入到队列的消息
     **/
    static public function move_doing_back($queue_name, $time_ago=600){
        $start = 0;
        $step = 100;
        $total = 0;
        do{
            $data_list = $GLOBALS['app']->redis()->lrange('doing_yiluphp_queue'.$queue_name, $start, $start+$step);
            if($data_list){
                foreach($data_list as $msg){
                    $tmp = json_decode($msg, true);
                    if($tmp && (time()-$tmp['ctime']) > $time_ago ){
                        //插入消息队列最后面
                        $count = $GLOBALS['app']->redis()->rpush('yiluphp_queue'.$queue_name, $msg);
                        if($count>0){
                            //从正在执行中删除
                            if (!$GLOBALS['app']->redis()->lrem('doing_yiluphp_queue'.$queue_name, $msg, 1)) {
                                //如果删除失败,则写错误日志
                                write_applog('ERROR', '从正在执行中删除元素失败,$queue_name:'.$queue_name.',:' . $msg);
                            }
                            else{
                                $total++;
                            }
                        }
                        else {
                            //写错误日志
                            write_applog('ERROR', '将卡在正在执行列表中的消息移回到消息队列中失败,$queue_name:'.$queue_name.',:' . $msg);
                        }
                    }
                }
            }
            $start += $step;
        }
        while($data_list);
        //返回成功移加的消息数量
        return $total;
    }
}
