<?php
/*
 * 根据url判断是否需要检查用户的登录状态
 * 把此类名加入到配置的before_controller中就可以实现所有页面都做检查的效果
 */

class hook_route_auth extends hook
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
        'post' => [
            '/^\/miniprogram\/login_by_code/',
            '/^\/miniprogram\/decrypt_mobile/',
        ],
	    'get_check' => [
            '/^\/(\?.*)*$/',
            '/^\/sign\/in/',
            '/^\/sign\/up/',
            '/^\/sign\/out/',
            '/^\/find_password/',
            '/^\/sign\/qq_callback/',
            '/^\/sign\/wechat_callback/',
            '/^\/sign\/alipay_callback/',
        ],
        'post_check' => [
            '/^\/user\/register/',
            '/^\/send_email_code/',
            '/^\/sign\/wechat_check_qr_login/',
        ],
        'get_post_internal' => [
            '/^\/internal\/check_login_by_uid/',
            '/^\/internal\/check_login_by_vk/',
            '/^\/internal\/check_login_by_tlt/',
            '/^\/internal\/find_user_info_by_uid/',
            '/^\/internal\/select_menu_list/',
            '/^\/internal\/find_uid_by_username/',
            '/^\/internal\/find_uid_by_identity/',
            '/^\/internal\/find_username_by_uid/',
            '/^\/internal\/insert_permission/',
            '/^\/internal\/check_user_permission/',
            '/^\/internal\/select_permission_users/',
            '/^\/internal\/delete_permission_by_key/',
        ],
        'get_login' => [
            '/^\/user\/list/',
            '/^\/user\/add/',
            '/^\/user\/forbidden/',
            '/^\/user\/logout/',
            '/^\/user\/detail/',
            '/^\/user\/grant_role/',
            '/^\/user\/grant_permission/',
            '/^\/complaint\/list/',
            '/^\/complaint\/detail/',
            '/^\/feedback\/list/',
            '/^\/feedback\/detail/',
            '/^\/dashboard/',
            '/^\/menus\/list/',
            '/^\/menus\/add/',
            '/^\/menus\/edit/',
            '/^\/setting\/user_info/',
            '/^\/setting\/brief_info/',
            '/^\/setting\/modify_avatar/',
            '/^\/setting\/modify_password/',
            '/^\/setting\/bind_email/',
            '/^\/application\/list/',
            '/^\/application\/add/',
            '/^\/application\/edit/',
            '/^\/application\/permission_list/',
            '/^\/application\/add_permission/',
            '/^\/application\/edit_permission/',
            '/^\/application\/permission_users/',
            '/^\/role\/list/',
            '/^\/role\/add/',
            '/^\/role\/edit/',
            '/^\/role\/grant_permission/',
            '/^\/role\/users/',
            '/^\/language\/project/',
            '/^\/language\/add_project/',
            '/^\/language\/edit_project/',
            '/^\/language\/table/',
        ],
        'post_login' => [
            '/^\/user\/save_add/',
            '/^\/menus\/save_add/',
            '/^\/menus\/save_edit/',
            '/^\/menus\/delete/',
            '/^\/complaint\/save_edit/',
            '/^\/feedback\/save_edit/',
            '/^\/setting\/save_info/',
            '/^\/setting\/save_avatar/',
            '/^\/setting\/save_password/',
            '/^\/setting\/save_email/',
            '/^\/setting\/unbind_wechat/',
            '/^\/setting\/unbind_qq/',
            '/^\/setting\/unbind_alipay/',
            '/^\/application\/save_add/',
            '/^\/application\/save_edit/',
            '/^\/application\/delete/',
            '/^\/application\/show_secret/',
            '/^\/application\/refresh_secret/',
            '/^\/application\/delete_permission/',
            '/^\/application\/save_add_permission/',
            '/^\/application\/save_edit_permission/',
            '/^\/role\/save_add/',
            '/^\/role\/save_edit/',
            '/^\/role\/save_delete_role_permission/',
            '/^\/role\/save_add_role_permission/',
            '/^\/role\/save_grant_permission/',
            '/^\/role\/delete/',
            '/^\/user\/save_add_role/',
            '/^\/user\/save_add_permission/',
            '/^\/user\/save_delete_role/',
            '/^\/user\/save_delete_permission/',
            '/^\/user\/change_user_status/',
            '/^\/user\/reset_user_password/',
            '/^\/language\/save_add_project/',
            '/^\/language\/save_edit_project/',
            '/^\/language\/delete_project/',
            '/^\/language\/pull_from_file/',
            '/^\/language\/write_to_file/',
            '/^\/language\/pull_from_js_file/',
            '/^\/language\/write_to_js_file/',
            '/^\/language\/delete_lang_key/',
            '/^\/language\/check_language_key_usable/',
            '/^\/language\/save_edit_lang_value/',
            '/^\/language\/save_lang_output_type/',
            '/^\/uploader\/form_image/',
            '/^\/uploader\/binary_image/',
        ],

	];

    public function run()
    {
    }

    public function __construct()
	{
	    //检查用户的访问标识
	    $this->check_vk();
	    //获取当前使用的请求方法
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		//获取当前url
        $request_uri = YiluPHP::I()->origin_uri();
		foreach($this->url_auth as $rules => $patterns){
            $rules = explode('_', $rules);
            foreach ($patterns as  $pattern) {
                if (preg_match($pattern, $request_uri)) {
                    if ((in_array('get', $rules) || in_array('post', $rules)) && !in_array($method, $rules)) {
                        //请求方法错误
                        throw new validate_exception(YiluPHP::I()->lang('request_method_error'), CODE_REQUEST_METHOD_ERROR);
                    }
                    if (in_array('check', $rules) || in_array('login', $rules)) {
                        //读出登录用户的资料
                        $user_info = logic_user::I()->get_current_user_info();
                        if (in_array('login', $rules) && !$user_info) {
                            //返回必须登录的提示
                            throw new validate_exception(YiluPHP::I()->lang('please_login'), CODE_USER_NOT_LOGIN);
                        }
                        if ($user_info) {
                            //把用户信息保存在全局变量中
                            $GLOBALS['self_info'] = $user_info;
                            logic_user::I()->keep_login_user_alive($user_info['uid'], $this->vk, TIME_30_MIN);
                            unset($user_info);
                        }
                    }
                    foreach ($rules as $rule){
                        if (!in_array($rule, ['get','post','check','login','guest'])){
                            $class_name = 'hook_'.$rule;
                            $class_name::I()->check();
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
            if (!empty($_REQUEST['vk'])){
                $vk = $_REQUEST['vk'];
            }
            else if(!empty($_SERVER['HTTP_VK'])){
                $vk = $_SERVER['HTTP_VK'];
            }
            else{
                $vk = create_unique_key();
            }
            $domain = isset($GLOBALS['config']['root_domain']) ? $GLOBALS['config']['root_domain'] : '';
            $_COOKIE['vk'] = $vk;
            $this->vk = $vk;
            setcookie('vk', $_COOKIE['vk'], time()+TIME_10_YEAR, '/', $domain);
        }
        else{
            $this->vk = $_COOKIE['vk'];
        }
        return;
    }

	public function __destruct()
	{
	}
}
