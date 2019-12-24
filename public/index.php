<?php
/*
 * 入口文件，所有的PHP请求请求从这里开始
 * 包含配置文件、公共函数库、创建$app实例、转发到指定的controller文件
 * 此文件中创建的实例$app，连接数据库和redis都通过此实例
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/12/27
 * Time: 19:21
 */

!isset($project_root) && $project_root = substr(dirname(__FILE__), 0, -6);
$config = require($project_root.'config/app.php');
//当前登录用户的基本信息
$self_info = null;

if(isset($config['error_level']) && is_integer($config['error_level'])) {
    error_reporting($config['error_level']);
}
//用户跟踪请求和返回的日志,id一样即同一次请求写的日志
$Yilu_request_id = rand(1000,999999);
//请求到达即写访问日志
write_applog('VISIT');
require($project_root.'functions.php');

//100-1000之内的错误码请留给YiluPHP官方使用
define('CODE_SUCCESS', 0);	//操作成功无错误
define('CODE_PARAM_ERROR', 100); //参数错误
define('CODE_REQUIRED_PARAM_ERROR', 101); //必选参数错误
define('CODE_NUMERIC_PARAM_ERROR', 102); //数字参数错误
define('CODE_INTEGER_PARAM_ERROR', 103); //整数参数错误
define('CODE_STRING_PARAM_ERROR', 104); //字符参数错误
define('CODE_ARRAY_PARAM_ERROR', 105); //数组参数错误
define('CODE_EMAIL_PARAM_ERROR', 106); //email参数错误
define('CODE_JSON_PARAM_ERROR', 107); //JSON参数错误
define('CODE_STRING_MIN_PARAM_ERROR', 108); //字符参数长度超出最小值错误
define('CODE_STRING_MAX_PARAM_ERROR', 109); //字符参数长度超出最大值错误
define('CODE_NUMERIC_MIN_PARAM_ERROR', 110); //数字参数超出最小值错误
define('CODE_NUMERIC_MAX_PARAM_ERROR', 111); //数字参数超出最大值错误
define('CODE_EQUAL_PARAM_ERROR', 112); //需要相等的参数错误
define('CODE_RSA_PARAM_ERROR', 113); //RSA加密参数错误
define('CODE_NO_AUTHORIZED', 120); //没有权限错误
define('CODE_UNDEFINED_ERROR_TYPE', 121); //没有定义的错误类型
define('CODE_PAGE_NOT_FIND', 404);	//页面找不到
define('CODE_SYSTEM_ERR', 500);	//系统错误
define('CODE_DB_ERR', 501);	//数据库错误
define('CODE_ERROR_IN_MODEL', 502); //model中的错误
define('CODE_ERROR_IN_SERVICE', 503); //model中的错误
define('CODE_REQUEST_METHOD_ERROR', 504); //请求方法错误

//设置需要使用的语言
if(isset($_REQUEST['lang']) && trim($_REQUEST['lang'])!='' ){
    $config['lang'] = strtolower(trim($_REQUEST['lang']));
    setcookie('lang', $config['lang'], 0, '/', empty($config['root_domain'])?'':$config['root_domain']);
}
else if(isset($_COOKIE['lang']) && trim($_COOKIE['lang'])!='' ){
    $config['lang'] = strtolower($_COOKIE['lang']);
}

/*
 * 获取系统版本号
 */
function get_version(){
    return 'YiluPHP-V1.0';
}

/*
 * 写项目内日志
 * $level 定义值：ERROR错误、WARNING警告、DEBUG调试、NOTICE通知、VISIT访问、RESPONSE响应(HTML的不写，只写json和jsonp)、TRACE代码追溯
 * 其它可以自定义，先在配置log_level中定义，然后写日志时把它传给level值就行了
 */
function write_applog(string $level, string $data='')
{
    if (empty($GLOBALS['config']['log_level']) || !is_array($GLOBALS['config']['log_level'])){
        return;
    }
    $level = strtoupper($level);
    if (!in_array($level, $GLOBALS['config']['log_level'])){
        return;
    }

    $datatime = '['.date('Y-m-d H:i:s').'] '.$level.': REQUEST_ID='.$GLOBALS['Yilu_request_id'].' ';
    if ($level == 'TRACE') {
        $txt = $datatime.$_SERVER['REQUEST_URI'].' , GET:'.json_encode($_GET,JSON_UNESCAPED_UNICODE).' POST:'.json_encode($_POST).' , RESPONSE: '.$data;
        $code = mb_detect_encoding($txt);
        if ($code!='UTF-8'){
            $txt = iconv($code, 'UTF-8', $txt);
        }
        $a = debug_backtrace();
        array_shift($a);
        foreach ($a as $value) {
            $txt .= "\n\t\t".'file:'.$value['file'].', line:'.$value['line'];
            unset($value['file'], $value['line']);
            $txt .= json_encode($value,JSON_UNESCAPED_UNICODE);
        }
    }
    else if ($level == 'VISIT') {
        $txt = $datatime.$_SERVER['REQUEST_URI'].' , GET: '.json_encode($_GET,JSON_UNESCAPED_UNICODE).' POST: '.json_encode($_POST,JSON_UNESCAPED_UNICODE).' $_SERVER: '.json_encode($_SERVER,JSON_UNESCAPED_UNICODE);
    }
    else if ($level == 'RESPONSE') {
        $txt = $datatime.$_SERVER['REQUEST_URI'].' , GET: '.json_encode($_GET,JSON_UNESCAPED_UNICODE).' POST: '.json_encode($_POST,JSON_UNESCAPED_UNICODE).' $_SERVER: '.json_encode($_SERVER,JSON_UNESCAPED_UNICODE).' , RESPONSE: '.$data;
    }
    else {
        $txt = $datatime.$_SERVER['REQUEST_URI'].' , GET: '.json_encode($_GET,JSON_UNESCAPED_UNICODE).' POST: '.json_encode($_POST,JSON_UNESCAPED_UNICODE).' $_SERVER: '.json_encode($_SERVER,JSON_UNESCAPED_UNICODE).' , RESPONSE: '.$data;
    }
    $path = $GLOBALS['project_root'].'logs/';
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
    $code = mb_detect_encoding($txt);
    if ($code!='UTF-8'){
        $txt = iconv($code, 'UTF-8', $txt);
    }
    openlog("qcloud_db",LOG_PID, LOG_LOCAL5);
    syslog(LOG_INFO, $txt);
    closelog();
    $file = $path.date('Y-m-d').'.log';
    file_put_contents($file, $txt."\n\n", FILE_APPEND);
    chmod($file,0755);
}

function connectMysql($options)
{
    try {
        $pdo = new PDO(
            $options['dsn'],
            $options['username'],
            $options['password'],
            $options['option']
        );
        // 设置 PDO 错误模式为异常 ，用于抛出异常
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if (isset($options['charset'])) {
            $pdo->exec('SET NAMES "'.$options['charset'].'"');
        }
        return $pdo;
    }
    catch (PDOException $e) {
        write_applog('ERROR', '连接数据库失败，错误信息：'.$e->getMessage()
            .'，连接参数：'.json_encode($options, JSON_UNESCAPED_UNICODE));
        throw new Exception($e->getMessage());
    }
}

function connectRedis($options)
{
    $redis = new Redis();
    $redis->pconnect($options['host'], $options['port']);
    return $redis;
}

/**
 * @name 往消息队列中增加消息
 * @desc 消息队列使用redis的列表实现
 * @param string $class_name 处理队列的类名即cli/queue/目录中的类
 * @param string/array/numeric/boolean $data 消息的内容,读取消息时将原样返回
 * @param string $queue_name 队列的名称,用户自己定义,例如email,sms
 * @param boolean $delay_second 延迟执行的秒数
 * @param boolean $to_first 是否优先处理,true为放到第一个处理
 * @return string 文件路径
 */
function add_to_queue($class_name, $data, $queue_name='default', $delay_second=0, $to_first=false)
{
    if(!$queue_name){
        write_applog('ERROR', '添加到消息队列时,消息队列的名称不正确');
        return_code(CODE_UNDEFINED_ERROR_TYPE,'添加到消息队列时,消息队列的名称不正确');
    }

    //如果是同步模式,则不写redis,直接运行队列文件
    if(!empty($GLOBALS['config']['queue_mode']) && $GLOBALS['config']['queue_mode']=='sync'){
        $file = $GLOBALS['project_root'].'cli/queue/'.$class_name.'.php';
        if(!file_exists($file)){
            write_applog('ERROR', '未找到消息列表的实现文件:'.$file);
            return_code(CODE_UNDEFINED_ERROR_TYPE,'未找到消息列表的实现文件:'.$file);
        }
        include_once $file;
        if(!class_exists($class_name)){
            write_applog('ERROR', '在文件'.$file.'中，未找到消息列表的实现类:class '.$class_name);
            return_code(CODE_UNDEFINED_ERROR_TYPE,'在文件'.$file.'中，未找到消息列表的实现类:class '.$class_name);
        }
        $queue = new $class_name();
        return $queue->run($data);
    }

    if(!$GLOBALS['app']->redis()->hexists('yiluphp_queue_list_for_manage', $queue_name)
        && !$GLOBALS['app']->redis()->hset('yiluphp_queue_list_for_manage', $queue_name, json_encode(['status'=>'running']))){
        is_array($data) && $data = json_encode($data);
        write_applog('ERROR', '添加到消息队列时,存入管理列表失败, $queue_name:'.$queue_name.',$to_first:'.($to_first?'true':'false').',$data:'.$data);
        return false;
    }
    $msg = [
        'ctime' => time(),
        'class_name' => $class_name,
        'data' => $data,
    ];
    if($delay_second>0){
        $msg['delay'] = $delay_second;
    }
    if($to_first){
        $res = $GLOBALS['app']->redis()->lpush('yiluphp_queue'.$queue_name, json_encode($msg));
    }
    else{
        $res = $GLOBALS['app']->redis()->rpush('yiluphp_queue'.$queue_name, json_encode($msg));
    }
    if($res===false){
        is_array($data) && $data = json_encode($data);
        write_applog('ERROR', '添加到消息队列时失败, $queue_name:'.$queue_name.',$to_first:'.($to_first?'true':'false').',$data:'.$data);
    }
    return $res;
}

function throw404()
{
    //抛出404
    header('HTTP/1.1 404 Not Found');
    header("status: 404 Not Found");
    if(isset($GLOBALS['app'])){
        unset($GLOBALS['app']);
    }
    exit;
}

/**
 * @name 返回结果给前端
 * @desc 返回结果可以是：输出html模板、JSON、JSONP，如果请求参数中有dtype参数，且值是JSON或JSONP，则返回其中之一的结果类型
 * @param string $template 模板路径及名称
 * @param array $data 需要输出的数据，即模板中使用到的数据
 * @param boolean $return_html 如果为true，不直接输出HTML，而是返回渲染后的HTML字符串
 * @return string 或 结束请求
 */
function return_result($template, $data=[], $return_html=false)
{
    //换成长名称，避免与传过来的参数名相同
    $YiluPHP['template_name'] = $template;
    $YiluPHP['data'] = $data;
    $YiluPHP['return_html'] = $return_html;
    unset($template, $data, $return_html);

    //可传参数with_layout决定最多允许的layout层次数,null为默认的20层,0表示不使用layout
    $YiluPHP['with_layout'] = $GLOBALS['app']->input->request_int('with_layout', null);
    if($YiluPHP['with_layout'] === null) {
        if (!$YiluPHP['template_name'] || (isset($_REQUEST['dtype']) && in_array(strtolower($_REQUEST['dtype']), ['json', 'jsonp']))) {
            unset($YiluPHP['template_name'], $YiluPHP['return_html']);
            if (strtolower($_REQUEST['dtype']) == 'jsonp') {
                return_jsonp(CODE_SUCCESS, 'success', $YiluPHP['data']);
            } else {
                return_json(CODE_SUCCESS, 'success', $YiluPHP['data']);
            }
        }
    }

    $YiluPHP['file'] = $GLOBALS['project_root'].'template/'.$YiluPHP['template_name'].'.php';
    if(!file_exists($YiluPHP['file'])) {
        unset($YiluPHP['template_name'], $YiluPHP['return_html']);
        return_code(CODE_UNDEFINED_ERROR_TYPE,'模板不存在：' . $YiluPHP['file']);
    }
    //取出数据
    extract($YiluPHP['data']);
    unset($YiluPHP['data']);
    extract($GLOBALS);

    ob_start(); //打开缓冲区
    include($YiluPHP['file']);
    $YiluPHP['cache_string']=ob_get_contents();
    ob_clean();

    //解析模板中使用到的布局
    $YiluPHP['check_layout_status'] = preg_match_all('/<!--\{use_layout\s+(\S+)\}-->\s*/', $YiluPHP['cache_string'], $YiluPHP['matches'], PREG_SET_ORDER);
    if(false === $YiluPHP['check_layout_status']){
        unset($YiluPHP['template_name'], $YiluPHP['check_layout_status'], $YiluPHP['matches'], $YiluPHP['cache_string'], $YiluPHP['return_html']);
        return_code(CODE_UNDEFINED_ERROR_TYPE,$GLOBALS['app']->lang('parsing_template_fail').'：'.$YiluPHP['file']);
    }
    if($YiluPHP['check_layout_status']>1){
        unset($YiluPHP['template_name'], $YiluPHP['check_layout_status'], $YiluPHP['matches'], $YiluPHP['cache_string'], $YiluPHP['return_html']);
        return_code(CODE_UNDEFINED_ERROR_TYPE,$GLOBALS['app']->lang('one_template_only_one_layout').'：'.$YiluPHP['file']);
    }
    $YiluPHP['loop_time'] = 0;
    //层层向上解析模板中使用到的布局
    while (count($YiluPHP['matches'])>0){
        if($YiluPHP['with_layout']!==null && $YiluPHP['with_layout']<=$YiluPHP['loop_time']){
            //去除模板中的调用布局的代码
            $YiluPHP['cache_string'] = str_replace($YiluPHP['matches'][0][0], '', $YiluPHP['cache_string']);
            break;
        }
        if($YiluPHP['loop_time']>20){
            unset($YiluPHP['template_name'], $YiluPHP['check_layout_status'], $YiluPHP['matches'], $YiluPHP['cache_string'], $YiluPHP['return_html'], $YiluPHP['loop_time']);
            //模板嵌套太多布局了
            return_code(CODE_UNDEFINED_ERROR_TYPE,$GLOBALS['app']->lang('too_many_nested_layouts').'：'.$YiluPHP['file']);
        }
        $YiluPHP['loop_time']++;

        $YiluPHP['file'] = $GLOBALS['project_root'].'template/'.$YiluPHP['matches'][0][1].'.php';
        if(!file_exists($YiluPHP['file'])) {
            unset($YiluPHP['template_name'], $YiluPHP['check_layout_status'], $YiluPHP['matches'], $YiluPHP['cache_string'], $YiluPHP['return_html']);
            return_code(CODE_UNDEFINED_ERROR_TYPE,$GLOBALS['app']->lang('layout_file_not_exists') . '：' . $YiluPHP['file']);
        }

        ob_start(); //打开缓冲区
        include($YiluPHP['file']);
        $YiluPHP['layout_cache_string']=ob_get_contents();
        ob_clean();

        //去除模板中的调用布局的代码
        $YiluPHP['cache_string'] = str_replace($YiluPHP['matches'][0][0], '', $YiluPHP['cache_string']);
        //检查layout中是否存在内容的占位符
        if(false === strpos($YiluPHP['layout_cache_string'], '<!--{$contents}-->')){
            unset($YiluPHP['template_name'], $YiluPHP['matches'], $YiluPHP['cache_string'], $YiluPHP['return_html'], $YiluPHP['layout_cache_string']);
            return_code(CODE_UNDEFINED_ERROR_TYPE,$GLOBALS['app']->lang('layout_file_have_no_content_replacer').'：'.$YiluPHP['file']);
        }

        //把模板插入到布局中
        $YiluPHP['cache_string'] = str_replace('<!--{$contents}-->', $YiluPHP['cache_string'], $YiluPHP['layout_cache_string']);
        $YiluPHP['check_layout_status'] = preg_match_all('/<!--\{use_layout\s+(\S+)\}-->\s*/', $YiluPHP['cache_string'], $YiluPHP['matches'], PREG_SET_ORDER);
        if(false === $YiluPHP['check_layout_status']){
            unset($YiluPHP['template_name'], $YiluPHP['check_layout_status'], $YiluPHP['matches'], $YiluPHP['cache_string'], $YiluPHP['return_html']);
            return_code(CODE_UNDEFINED_ERROR_TYPE,$GLOBALS['app']->lang('parsing_template_fail').'：'.$YiluPHP['file']);
        }
        if($YiluPHP['check_layout_status']>1){
            unset($YiluPHP['template_name'], $YiluPHP['check_layout_status'], $YiluPHP['matches'], $YiluPHP['cache_string'], $YiluPHP['return_html']);
            return_code(CODE_UNDEFINED_ERROR_TYPE,$GLOBALS['app']->lang('one_template_only_one_layout').'：'.$YiluPHP['file']);
        }
        unset($YiluPHP['layout_cache_string']);
    }

    unset($YiluPHP['template_name'], $YiluPHP['file'], $YiluPHP['check_layout_status'], $YiluPHP['matches']);
    if($YiluPHP['return_html']){
        return $YiluPHP['cache_string'];
    }
    if($YiluPHP['with_layout'] !== null) {
        if (isset($_REQUEST['dtype']) && in_array(strtolower($_REQUEST['dtype']), ['json', 'jsonp'])) {
            $data = [
                'html'=>$YiluPHP['cache_string']
            ];
            if (isset($head_info)){
                $data['head_info'] = $head_info;
            }
            if (strtolower($_REQUEST['dtype']) == 'jsonp') {
                return_jsonp(CODE_SUCCESS, 'success', $data);
            } else {
                return_json(CODE_SUCCESS, 'success', $data);
            }
        }
    }
    echo $YiluPHP['cache_string'];
    unset($YiluPHP);
    after_controller();
    if(isset($GLOBALS['app'])){
        unset($GLOBALS['app']);
    }
    exit;
}

function is_debug_mode(){
    $client_ip = client_ip();
    if (!empty($GLOBALS['config']['debug_client_ip'])){
        $debug_client_ip = $GLOBALS['config']['debug_client_ip'];
        if (is_array($debug_client_ip) && in_array($client_ip,$debug_client_ip)){
            return true;
        }
        if ($debug_client_ip==$client_ip){
            return true;
        }
    }
    return false;
}

/**
 * @name 返回提示信息给前端
 * @desc 如：成功、未登录提示、参数错误、系统错误之类的返回
 * @param integer $code 状态码，建议无错误则返回0
 * @param string $msg 描述信息
 * @param array $data 需要输出的数据
 * @return json/jsonp/html
 */
function return_code($code, $msg='', $data=[])
{
    //在非调试模式下，对外不显示详细的内部错误信息
    if(!is_debug_mode() && !empty($GLOBALS['config']['inner_error_code'][0])
        && !empty($GLOBALS['config']['inner_error_code'][1])
        && $code>=$GLOBALS['config']['inner_error_code'][0]
        && $code<=$GLOBALS['config']['inner_error_code'][1]){
        $msg = $GLOBALS['app']->lang('inner_error');
    }

    if(isset($_REQUEST['dtype']) && in_array(strtolower($_REQUEST['dtype']), ['json', 'jsonp'])){
        if(strtolower($_REQUEST['dtype'])=='json'){
            return_json($code, $msg, $data);
        }
        if(strtolower($_REQUEST['dtype'])=='jsonp'){
            return_jsonp($code, $msg, $data);
        }
    }
    echo '<meta charset="utf-8">';
    $data = [
        'err_code' => $code,
        'msg' => $msg,
        'data' => $data,
    ];
    if(is_debug_mode()){
        //输出程序运行回溯路径
        $data['backtrace'] = debug_backtrace();
    }
    unset($code,$msg);
    return_result('show_msg', $data);
}

/**
 * @name 以JSON格式返回结果给前端
 * @desc
 * @param integer $code 状态码，建议无错误则返回0
 * @param string $msg 描述信息
 * @param array $data 需要输出的数据
 * @return json
 */
function return_json($code, $msg='', $data=[])
{
    //在非调试模式下，对外不显示详细的内部错误信息
    if(!is_debug_mode() && !empty($GLOBALS['config']['inner_error_code'][0])
        && !empty($GLOBALS['config']['inner_error_code'][1])
        && $code>=$GLOBALS['config']['inner_error_code'][0]
        && $code<=$GLOBALS['config']['inner_error_code'][1]){
        $msg = $GLOBALS['app']->lang('inner_error');
    }
    $res = ['code'=>$code, 'msg'=>$msg, 'data'=>$data];
    if(is_debug_mode()){
        //输出程序运行回溯路径
        $res['backtrace'] = debug_backtrace();
    }
    $res = json_encode($res, JSON_UNESCAPED_UNICODE);
    write_applog('RESPONSE', $res);
    echo $res;
    after_controller();
    if(isset($GLOBALS['app'])){
        unset($GLOBALS['app']);
    }
    unset($res);
    exit;
}

/**
 * @name 以JSONP格式返回结果给前端
 * @desc
 * @param integer $code 状态码，建议无错误则返回0
 * @param string $msg 描述信息
 * @param array $data 需要输出的数据
 * @return json
 */
function return_jsonp($code, $msg='', $data=[])
{
    //在非调试模式下，对外不显示详细的内部错误信息
    if(!is_debug_mode() && !empty($GLOBALS['config']['inner_error_code'][0])
        && !empty($GLOBALS['config']['inner_error_code'][1])
        && $code>=$GLOBALS['config']['inner_error_code'][0]
        && $code<=$GLOBALS['config']['inner_error_code'][1]){
        $msg = $GLOBALS['app']->lang('inner_error');
    }
    $backtrace = 'null';
    if(is_debug_mode()){
        //输出程序运行回溯路径
        $backtrace = debug_backtrace();
        $backtrace = json_encode($backtrace);
    }
    $fun = isset($_REQUEST['callback']) && trim($_REQUEST['callback'])!=='' ? trim($_REQUEST['callback']) : 'callback';
    $data = is_array($data)?json_encode($data, JSON_UNESCAPED_UNICODE):[];
    $res = $fun.'('.$code.', "'.htmlspecialchars($msg).'", '.$data.', '.$backtrace.');';
    write_applog('RESPONSE', $res);
    echo $res;
    after_controller();
    if(isset($GLOBALS['app'])){
        unset($GLOBALS['app']);
    }
    unset($res);
    exit;
}

/**
 * 执行后置类
 * 返回数据给前端之后执行配置的类
 */
function after_controller()
{
    if(!empty($GLOBALS['config']['after_controller']) && is_array($GLOBALS['config']['after_controller'])){
        foreach($GLOBALS['config']['after_controller'] as $class_name){
            $GLOBALS['app']->$class_name;
        }
    }
}
if(!empty(env()) && in_array(strtolower(env()), ['dev', 'local'])){
    include_once 'useful_cheat.php';
}
else{
    eval('trait useful_cheat{}');
}

class YiluPHP
{
    use useful_cheat;
    public $mysql_list = [];
    public $redis_list = [];
    public $helper = [];
    protected $lang = [];
    protected $page_lang = [];
    public $autoload_class = null;

    public function __construct()
    {
        $this->autoload_class = function ($class_name){
            $file = $GLOBALS['project_root'].'helper/'.$class_name.'.php';
            if (file_exists($file)) {
                //helper类文件的文件名、类名、app中的调用方法三者需要一致
                require_once($file);
                return $class_name;
            }

            //将驼峰式的名称用下划线分割
            $path = preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $class_name);
            $path = explode('_', $path, 2);
            $path = $path[0].'/'.$class_name;
            $file = $GLOBALS['project_root'].$path.'.php';
            if (file_exists($file)) {
                //类文件的文件名、类名、app中的调用方法三者需要一致
                require_once($file);
                return $class_name;
            }

            //支持给类取别名
            if(!empty($GLOBALS['config']['helper_alias']) && array_key_exists($class_name, $GLOBALS['config']['helper_alias']) ){
                $real_class_name = $GLOBALS['config']['helper_alias'][$class_name];
                $file = $GLOBALS['project_root'].'helper/'.$real_class_name.'.php';
                if (file_exists($file)) {
                    require_once($file);
                    return $real_class_name;
                }

                //将驼峰式的名称用下划线分割
                $path = preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $real_class_name);
                $path = explode('_', $path, 2);
                $path = $path[0].'/'.$real_class_name;
                $file = $GLOBALS['project_root'].$path.'.php';
                if (file_exists($file)) {
                    require_once($file);
                    return $real_class_name;
                }
            }
            return false;
        };
    }

    public function __destruct()
    {
        foreach ($this->mysql_list as $key => &$pdo) {
            $pdo = null;
            unset($this->mysql_list[$key]);
        }
        foreach ($this->redis_list as $key => &$redis) {
            $redis->close();
            unset($this->redis_list[$key]);
        }
    }

    /**
     * 检查连接是否可用
     * @param Link $dbconn 数据库连接
     * @return Boolean
     */
    private function _pdo_ping($dbconn){
        try{
            $dbconn->getAttribute(PDO::ATTR_SERVER_INFO);
        } catch (PDOException $e) {
            if(strpos($e->getMessage(), 'MySQL server has gone away')!==false){
                return false;
            }
        }
        return true;
    }

    /**
     * 获取一个MySQL连接实例
     * @access public
     * @param string $db_key 在配置文件中，数据库配置使用的键名
     * @return object 返回已经建立连接好的对象
     */
    public function mysql($db_key='default')
    {
        $db_key = empty($db_key)?'default':$db_key;
        if (!isset($GLOBALS['config']['mysql'][$db_key]) ) {
            throw new Exception('MySQL数据库配置不存在：$config[\'mysql\'][\''.$db_key.'\']');
        }
        if(isset($this->mysql_list[$db_key])){
            //检查连接是否断开
            if(!$this->_pdo_ping($this->mysql_list[$db_key])){
                unset($this->mysql_list[$db_key]);
            }
        }
        if ( !isset($this->mysql_list[$db_key]) && isset($GLOBALS['config']['mysql'][$db_key]) ) {
            $this->mysql_list[$db_key] = connectMysql($GLOBALS['config']['mysql'][$db_key]);
        }
        return $this->mysql_list[$db_key];
    }

    /**
     * @name 获取一个Redis连接实例
     * @desc 获取一个Redis连接实例
     * @param string $redis_config_key 在配置文件中，Redis配置使用的键名
     * @param integer $db 切换到指定的数据库，数据库索引号用数字值指定，以 0 作为起始索引值,默认的配置中最多15个库
     * @return object 返回已经建立连接好的对象
     * @throws \Exception
     */
    public function redis($redis_config_key='default', $db=0)
    {
        $redis_config_key = empty($redis_config_key)?'default':$redis_config_key;
        if (!isset($GLOBALS['config']['redis'][$redis_config_key]) ) {
            throw new Exception('Redis配置不存在：$config[\'redis\'][\''.$redis_config_key.'\']');
        }
        if(isset($this->redis_list[$redis_config_key])){
            //检查连接是否断开
            if($this->redis_list[$redis_config_key]->ping()!=='+PONG'){
                unset($this->redis_list[$redis_config_key]);
            }
        }
        if ( !isset($this->redis_list[$redis_config_key]) ) {
            $this->redis_list[$redis_config_key] = connectRedis($GLOBALS['config']['redis'][$redis_config_key]);
        }
        $this->redis_list[$redis_config_key]->select($db);
        return $this->redis_list[$redis_config_key];
    }

    /**
     * 获取当前用户使用的语言
     * @return string
     */
    public function current_lang()
    {
        if (empty($GLOBALS['config']['lang'])) {
            $GLOBALS['config']['lang'] = 'cn'; //默认为中文
        }
        return $GLOBALS['config']['lang'];
    }

    /**
     * 从针对公共的语言包中获取翻译结果
     * @param string $lang_key 语言的键名
     * @param array $data 替换变量的参数
     * @return string
     */
    public function lang($lang_key, $data=[])
    {
        $this->current_lang();
        global $app, $project_root;
        $res = $lang_key;
        if(!$this->lang){
            //载入OnoWayPHP系统语言包
            $file = $project_root.'system/lang/'.$GLOBALS['config']['lang'].'.php';
            if(file_exists($file)){
                $this->lang = require_once($file);
            }
            else{
                $this->lang = require_once($project_root.'system/lang/cn.php');
            }
            //载入用户的语言包
            $file = $project_root.'lang/'.$GLOBALS['config']['lang'].'.php';
            if(file_exists($file)){
                $this->lang = array_merge(require_once($file), $this->lang);
            }
            else if(file_exists($project_root.'lang/cn.php')){
                $this->lang = array_merge(require_once($project_root.'lang/cn.php'), $this->lang);
            }
            else{
                return_code(CODE_UNDEFINED_ERROR_TYPE,$app->lang('no_translation_file'). '：/lang/'.$GLOBALS['config']['lang'].'.php');
            }
        }
        if(!isset($this->lang[$lang_key])){
            //如果指定的翻译没有，则尝试使用中文的
            if( $GLOBALS['config']['lang']!=='cn' && file_exists($project_root.'lang/cn.php')){
                $lang = require $project_root.'lang/cn.php';
                if(isset($lang[$lang_key])){
                    $res = $lang[$lang_key];
                }
            }
            else{
                $res = $lang_key;
                write_applog('ERROR', $app->lang('no_translation'). '('.$GLOBALS['config']['lang'].')：'.$lang_key);
//                return_code(CODE_UNDEFINED_ERROR_TYPE,$app->lang('no_translation'). '('.$GLOBALS['config']['lang'].')：'.$lang_key);
            }
        }
        else{
            $res = $this->lang[$lang_key];
        }
        if($data){
            //替换字符串中的变量
            foreach($data as $key => $val){
                if(is_array($val)){
                    //如果$val是数组，则是需要单数和复数形式的翻译
                    $val['value'] = floatval($val['value']);
                    if($val['value']<=1){
                        $res = preg_replace('/<--singular(.*?)\{\$'.$key.'\}(.*?)-->/', '${1}'.$val['value'].'$2', $res);
                        $res = preg_replace('/<--plural(.*?)\{\$'.$key.'\}(.*?)-->/', '', $res);
                    }
                    else{
                        $res = preg_replace('/<--plural(.*?)\{\$'.$key.'\}(.*?)-->/', '${1}'.$val['value'].'$2', $res);
                        $res = preg_replace('/<--singular(.*?)\{\$'.$key.'\}(.*?)-->/', '', $res);
                    }
                    $res = preg_replace('/\{\$'.$key.'\}/i', $val['value'], $res);
                }
                else{
                    $res = preg_replace('/\{\$'.$key.'\}/i', $val, $res);
                }
            }
        }
        return stripslashes(stripslashes($res));
    }

    /**
     * 从针对指定页面的语言包中获取翻译结果
     * @param string $lang_key 语言的键名
     * @return string
     */
    public function page_lang($lang_key, $data=[])
    {
        if(!isset($this->page_lang[$lang_key])){
            return_code(CODE_UNDEFINED_ERROR_TYPE,$GLOBALS['app']->lang('no_translation'). '('.$GLOBALS['config']['lang'].')：'.$lang_key);
        }
        return $this->page_lang[$lang_key];
    }

    /**
     * 载入针对指定页面的语言包
     * @param string $lang_file 语言包的名称，语言包放在/lang/目录下
     * @return boolean
     */
    public function load_page_lang($lang_file)
    {
        $file = $GLOBALS['project_root'].'lang/'.$lang_file.'.php';
        if(file_exists($file)){
            $this->page_lang = array_merge(require_once($file), $this->page_lang);
        }
        else{
            return_code(CODE_UNDEFINED_ERROR_TYPE,$GLOBALS['app']->lang('file_not_exists'). '：'.$file);
        }
    }

    /**
     * 查看当前语言所有的翻译
     * @return string
     */
    public function view_lang()
    {
        echo '<div>公共语言包('.$GLOBALS['config']['lang'].')：</div><table style="background-color: #d9e7a8;"><tr>';
        ksort($this->lang);
        foreach($this->lang as $key => $val){
            echo '<td><strong>'.$key.'</strong></td><td>'.$val.'</td></tr>';
        }
        echo '</tr></table><div>页面语言包('.$GLOBALS['config']['lang'].')：</div><table style="background-color: #d9e7a8;"><tr>';
        ksort($this->page_lang);
        foreach($this->page_lang as $key => $val){
            echo '<td><strong>'.$key.'</strong></td><td>'.$val.'</td></tr>';
        }
        echo $this->page_lang ? '' : '<td><strong>&nbsp;</strong></td><td>没有数据&nbsp;</td></tr>';
        echo '</tr></table>';
    }

    public function __get($name)
    {
        if (isset($this->helper[$name])) {
            return $this->helper[$name];
        }
        $fun = $this->autoload_class;
        $class_name = $fun($name);
        unset($fun);
        if ($class_name!==false){
            $this->helper[$name] = new $class_name;
            return $this->helper[$name];
        }
        throw new Exception($this->lang('class_not_found').$name);
    }

    private function _class_name_to_path(string $name){
        $name = explode('_', $name, 2);
        if (count($name)>1){
            return $name[0].'/'.$name[1];
        }
        else{
            return $name[0].'/'.$name[0];
        }
    }

    public function __call($name, $arguments)
    {
        $file = 'helper/'.$name.'.php';
        if (file_exists($file)) {
            //helper类文件的文件名、类名、app中的调用方法三者需要一致
            require_once($file);
            return new $name;
        }
    }
}
$app = new YiluPHP();

if(!empty($config['use_session'])) {
    /**
     * 初始化SESSION工作
     * @desc 先在php.ini中设置session.save_handler的值为user，表示用户自定义session仿函数
     * @return boolean
     */
    function sess_begin(){
//    echo 'session begin';
        return true;
    }

    /**
     * 使用SESSION 结尾工作
     * @return boolean
     */
    function sess_end(){
//    echo 'session end';
        return true;
    }

    /**
     * 读SESSION操作
     * @param string $sess_id session的id
     * @return mixed
     */
    function sess_read($sess_id){
//    echo 'session read';
        $data = $GLOBALS['app']->redis()->get('SESSION_PREFIX:'.$sess_id);
        return is_string($data) ? $data : '';
    }

    /**
     * 写SESSION操作
     * @param string $sess_id session的id
     * @return string $sess_content session的内容
     * @return boolean
     */
    function sess_write($sess_id, $sess_content){
//    echo 'session write';
        $maxlifetime = ini_get('session.gc_maxlifetime');
        if(empty($GLOBALS['app'])){
            $GLOBALS['app'] = new YiluPHP();
        }
        $GLOBALS['app']->redis()->set('SESSION_PREFIX:'.$sess_id, $sess_content);
        $GLOBALS['app']->redis()->expire('SESSION_PREFIX:'.$sess_id, $maxlifetime);
        return true;
    }

    /**
     * 删除SESSION操作
     * @param string $sess_id session的id
     * @return mixed
     */
    function sess_delete($sess_id){
//    echo 'session delete';
        $GLOBALS['app']->redis()->del('SESSION_PREFIX:'.$sess_id);
        return true;
    }

    /**
     * SESSION的垃圾回收操作
     * @desc 删除过期的session，php会定期调用此函数
     * @param integer $maxlifetime session的存活时间，可以在php.ini中修改session.gc_maxlifetime，默认为1440，单位为秒
     * @return boolean
     */
    function sess_gc($maxlifetime){
//    echo 'session gc';
        //这里是给redis设置有效期，redis自动失效
        return true;
    }

    session_set_save_handler('sess_begin', 'sess_end', 'sess_read', 'sess_write', 'sess_delete', 'sess_gc');
    session_start();
}

/**
 * @name 获取当前环境标识
 * @desc 优先从配置文件中读取env的值，如果在配置中没有设置，就去/data/config/env文件中读，如果没有则返回false
 * @return false/string
 * dev代表开发环境，alpha代表测试环境，beta代表预发环境，idc或不设置代表线上（生产）环境
 */
function env(){
    if (!empty($GLOBALS['config']['env'])){
        return $GLOBALS['config']['env'];
    }
    if (file_exists('/data/config/env')){
        return trim(file_get_contents('/data/config/env'));
    }
    return 'idc';
}

/**
 * 获取指定目录下的目录和文件，不递归
 * @desc
 * @param string $path 指定的目录
 * @param string $type 需要获取的类型，all包含目录和文件，file仅返回文件，dir仅返回目录
 * @return boolean
 */
function get_dir_and_file($path='./', $type='all'){
    $type = strtolower($type);
    $file_array = $dir_array = [];
    //遍历目录下面的所有文件和目录，2019年2月15日
    $str = opendir($path);//指定获取此目录下的文件及文件夹列表
    while( ($filename = readdir($str)) !== false )
    {
        if($filename != "." && $filename != "..")
        {
            //判断是否是文件，文件放在文件列表数组中，子文件夹放在子文件夹列表数组中
            if (is_file($path.'/'.$filename)){
                if (in_array($type, ['all', 'file'])){
                    $file_array[]=$filename;
                }
            }else{
                if (in_array($type, ['all', 'dir'])){
                    $dir_array['d'.count($dir_array)]=$filename;
                }
            }
        }
    }
    closedir($str);
    $res = $dir_array + $file_array;
    unset($dir_array, $file_array, $path, $type, $str);
    return $res;
}


/**
 * 在指定目录下查找一个文件，递归查找
 * @desc
 * @param string $path 指定的目录
 * @param string $filename 文件名，包含后缀
 * @return string
 */
function find_file_in_dir($path, $filename){
    strpos($path, -1, 1)!='/' && $path .='/';
    if (file_exists($path.$filename)){
        return $path.$filename;
    }
    else{
        if($dir_list = get_dir_and_file($path, 'dir')){
            foreach ($dir_list as $item){
                if (file_exists($path.$item.'/'.$filename)){
                    return $path.$item.'/'.$filename;
                }
                else{
                    return find_file_in_dir($path.$item.'/', $filename);
                }
            }
        }
        else{
            return '';
        }
    }
}

/**
 * 类的自动加载
**/
spl_autoload_register($app->autoload_class);

if(PHP_SAPI=='cli'){
    //解析参数，传参数方式：在php文件名的加空格 再加用双引号包含的querystring格式的参数，例如：
    //php like_post.php "aa=aaaaa&bb=bbbbb"
    //在php文件中用$_GET或$_REQUEST使用参数
    foreach($argv as $key=>$arg){
        if($key>0){
            $tmp = explode('&', $arg);
            foreach($tmp as $val){
                $tmp2 = explode('=', $val, 2);
                if(trim($tmp2[0])!==''){
                    $_GET[trim($tmp2[0])] = $_REQUEST[trim($tmp2[0])] = $_POST[trim($tmp2[0])] = isset($tmp2[1])?$tmp2[1]:'';
                }
            }
        }
    }
    unset($arg, $key, $val, $tmp, $tmp2);
}
else{
    //如果有需要在执行controller之前调用的helper类则在此执行
    //例如防csrf攻击、验证访问权限等等，可在配置文件中配置多个类，每个类必须包含run方法，因为从此方法开始执行
    if(!empty($config['before_controller']) && is_array($config['before_controller'])){
        foreach($config['before_controller'] as $class_name){
            $app->$class_name;
        }
    }

    //解析URL路由和参数
    $url = explode('?', $_SERVER['REQUEST_URI']);
    $request_uri = $url[0];
    if($request_uri!='/' && !empty($config['rewrite_route'])){
        foreach ($config['rewrite_route'] as $key => $value){
            $key = preg_replace(['/\//', '/\./'], ['\/', '\.'],$key);
            preg_match_all("/(\{[^\/]+?\})/", $key, $matches);
            if (count($matches[1])>0){
                $key = preg_replace('/\{[^\/]+?\}/', '(.+?)',$key);
            }
            if(preg_match_all("/^".$key."$/", $url[0], $matches2)){
                //当前url与配置匹配对了
                if (count($matches[1])>0){
                    foreach ($matches[1] as $index => $item){
                        $matches[1][$index] = str_replace('{', '/\{', $item);
                        $matches[1][$index] = str_replace('}', '\}/', $matches[1][$index]);
                    }
                    $matches2_format = [];
                    foreach ($matches2 as $index => $item){
                        if ($index>0){
                            $matches2_format[] = $item[0];
                        }
                    }
                    //如果有变量，则替换之
                    $value = preg_replace($matches[1], $matches2_format, $value);
                }
                $request_uri = $value;
                unset($key, $value, $matches, $matches2);
                break;
            }
        }
        unset($key, $value, $matches, $matches2);
    }
    //继续解析路由,如果有路由需要做映射,则在上面设置的前置类中修改$url[0]的值
    if($request_uri=='/' && !empty($config['default_controller'])){
        $request_uri = [$config['default_controller']];
    }
    else{
        $request_uri = explode('/', strtolower($request_uri));
    }
    $file = $project_root.'controller/';
    $index = 0;
    $is_find_file = false;
    foreach($request_uri as $key => $val){
        //找到文件以后的数据都作为GET参数
        if($is_find_file){
            $index++;
            if($index%2 == 0){
                continue;
            }
            $_REQUEST[$val] = $_GET[$val] = isset($request_uri[$key+1]) ? $request_uri[$key+1] : '';
        }
        else if($val !== ''){
            if(file_exists($file.$val.'.php')){
                $is_find_file = true;
                $file = $file.$val.'.php';
            }
            else{
                $file .= $val.'/';
            }
            //允许最多2级目录名
            if($index>2 && !$is_find_file){
                throw404();
            }
            $index++;
            if($is_find_file){
                $index = 0;
            }
        }
    }
    if(!$is_find_file){
        throw404();
    }
    unset($index, $key, $val, $is_find_file, $url, $request_uri);
    require($file);
}
