<?php
/*
 * 根据url判断是否需要检查用户的登录状态
 * 把此类名加入到配置的before_controller中就可以实现所有页面都做检查的效果
 */

class hook_route_auth
{
	private $vk = null; //访问用户的标识符visit key,存在cookie
	private $lk = null; //登录用户的标识符login key,存在cookie

	//键名为匹配url的正则表达式
	private $url_auth = [
        /*
         * 请求方法有：get和post，没有设置请求方法则表示用任意一种方法都可以
         *
         * guest用户不用登录，没有配置的url默认为此值
         * check检查用户登录状态，如果登录了就读取用户的信息，没有登录也可以访问
         * login用户必须登录才能访问
         * */
	    'get_check' => [
            '/^\/(\?.*)*$/',
        ],
        'post_check' => [
        ],
        'get_login' => [
            '/^\/static_file\/project_list/',
        ],
        'post_login' => [
            '/^\/static_file\/save_add_project/',
        ],

	];

    public function __construct()
    {
        global $app;
        //检查用户的访问标识
        $this->check_vk();
        //检查url中是否有tlt参数
        $tlt = isset($_GET['tlt'])?$_GET['tlt']:'';
        if ($tlt){
            //如果没有登录则登录
            if (!$user_info = model_user_center::I()->get_current_user_info()){
                $user_info = model_user_center::I()->check_login_by_tlt($tlt);
                if (empty($user_info) || !is_array($user_info)){
                    $GLOBALS['dialog_error'] = YiluPHP::I()->lang('login_failed');
                }
                else if ($user_info['code']==-1){
                    $GLOBALS['dialog_error'] = YiluPHP::I()->lang('login_failed_or_timed_out');
                }
                else if ($user_info['code']==0){
                    //缓存用户登录的信息
                    redis_y::I()->hmset(REDIS_LOGIN_USER_INFO.$this->vk, $user_info['data']['user_info']);
                    redis_y::I()->expire(REDIS_LOGIN_USER_INFO.$this->vk, TIME_30_SEC);
                    //记录当前登录用户的UID
                    redis_y::I()->set(REDIS_LAST_LOGIN_UID.$this->vk, $user_info['data']['user_info']['uid']);
                    redis_y::I()->expire(REDIS_LAST_LOGIN_UID.$this->vk, TIME_DAY);

                    //去除tlt参数后重新加载
                    header('Location:'.delete_url_params(get_host_url(), ['tlt']));
                    exit;
                }
                else if ($user_info['code']>0){
                    $GLOBALS['dialog_error'] = $user_info['msg'].'('.$user_info['code'].')';
                    return_code(CODE_SYSTEM_ERR, $GLOBALS['dialog_error']);
                }
            }
            else{
                //去除tlt参数后重新加载
                header('Location:'.delete_url_params(get_host_url(), ['tlt']));
                exit;
            }
        }
        //获取当前使用的请求方法
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        //获取当前url
        foreach($this->url_auth as $rules => $patterns){
            $rules = explode('_', $rules);
            foreach ($patterns as  $pattern) {
                if (preg_match($pattern, $_SERVER['REQUEST_URI'])) {
                    if ((in_array('get', $rules) || in_array('post', $rules)) && !in_array($method, $rules)) {
                        //请求方法错误
                        return_code(CODE_REQUEST_METHOD_ERROR, YiluPHP::I()->lang('request_method_error'));
                    }
                    if (in_array('check', $rules) || in_array('login', $rules)) {
                        //读出登录用户的资料
                        $user_info = model_user_center::I()->get_current_user_info();
                        if (in_array('login', $rules) && !$user_info) {
                            return_code(CODE_USER_NOT_LOGIN, YiluPHP::I()->lang('please_login'));
                        }
                        if ($user_info) {
                            //把用户信息保存在全局变量中
                            $GLOBALS['self_info'] = $user_info;
                            unset($user_info);
                        }
                    }
                }
            }
        }
    }

    public function check_vk()
    {
        //vk即visit key，存在客户端的，用户访问系统的唯一标识
        if (!isset($_COOKIE['vk'])){
            $domain = isset($GLOBALS['config']['root_domain']) ? $GLOBALS['config']['root_domain'] : '';
            $_COOKIE['vk'] = create_unique_key();
            setcookie('vk', $_COOKIE['vk'], time()+TIME_10_YEAR, '/', $domain);
        }
        $this->vk = $_COOKIE['vk'];
        return;
    }

    public function __destruct()
    {
    }
}
