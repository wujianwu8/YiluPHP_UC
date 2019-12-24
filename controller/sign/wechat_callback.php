<?php
/**
 * @name 微信公众平台（服务号）授权登录的回调地址
 * @desc
 * @method GET
 * @uri /sign/wechat_callback
 * @param string code 授权码   必选    详细参考微信公众平台（服务号）授权登录的文档
 * @param string state 状态码  必选   用于防止CSRF攻击的字符串，详细参考微信公众平台（服务号）授权登录的文档
 * @return 跳转页面或显示“登录成功”
 * @exception   会在界面上显示错误
 */

$open = $app->input->get_int('open', 0);
$app->oauth_wechat->check_callback();
if ($open){
    $app->oauth_wechat->load_config('wechat_open');
}
$token = $app->oauth_wechat->get_access_token();
if(!$token || isset($token['errcode'])){
    return_code(CODE_UNDEFINED_ERROR_TYPE,'您已登录，如需再登录，请回登录页面重新操作');
}

//记录下传过来的code
$state = $app->input->get_trim('state', '');
//是否为二维码登录
$is_qrcode = false;
if(!empty($_SESSION['weixin_qr_login_code']) && strpos($state, 'weixin_qr_')===0){
    $is_qrcode = true;
}

$openid = empty($token['unionid'])?$token['openid']:$token['unionid'];
//如果是登录用户绑定第三方账号，走此流程
if ($app->input->get_int('for_bind', null)){
    $app->logic_user->bind_outer_account('WX', $openid);
}
//如果用户已经注册,则直接跳转
if($uid = $app->model_user_identity->find_uid_by_identity('WX', $openid)) {
    //更新用户access_token的值与有效期
    $where = [
        'uid' => $uid,
        'type' => 'WX',
        'identity' => $token['openid'],
    ];
    $data = [
        'access_token' => $token['access_token'],
        'expires_at' => time()+$token['expires_in'],
        'refresh_token' => $token['refresh_token'],
    ];
    $app->model_user_identity->update_table($where, $data);
    if ($is_qrcode){
        $data = [
            'openid' => $openid,
            'identity_type' => 'WX',
            'sid' => session_id(),
            'scan_sid' => session_id(),
            'ip' => client_ip(),
            'ctime' => time(),
        ];
        $code = $_SESSION['weixin_qr_login_code'];
        $_SESSION['weixin_qr_login_code']=null;
        $login_info = $app->redis()->get(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code);
        $login_info = json_decode($login_info, true);
        $login_info['status'] = 'login';
        $login_info = $data+$login_info;
        $app->redis()->set(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code, json_encode($login_info));
        //延长二维码的有效期
        $app->redis()->expire(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code, TIME_30_SEC);
        unset($login_info, $data, $state, $token);
        return_code(CODE_SUCCESS,'登录成功');
    }
    else {
        //登录用户
        $user_info = $app->logic_user->login_by_uid($uid);
        unset($login_info, $data, $state, $token);
        $app->logic_user->auto_jump(false, $user_info['tlt']);
    }
}

$app->oauth_wechat->set_access_token($token['access_token']);
$app->oauth_wechat->set_openid($token['openid']);

//array(9) {
//    ["openid"]=> string(28) "ogAIxwHFVLLr-ta0QD11HqU7aykA"
//    ["unionid"]=> string(28) "oarmLtwxXPNOcjydCy3eDbroXgLI"
//    ["nickname"]=> string(6) "Jim.Wu"
//    ["sex"]=> int(1) 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
//    ["language"]=> string(5) "zh_CN"
//    ["city"]=> string(6) "深圳"
//    ["province"]=> string(6) "广东"
//    ["country"]=> string(6) "中国"
//    ["headimgurl"]=> string(132) "http://thirdwx.qlogo.cn/mmopen/vi_32/rdpX04rcx6GBcCqsrfuMVtGibbb4jHhpT6jfVW1g4aowiaEDmMMEOFD7icH7sQ1LGib5C9nibtYfMNYdEhVcd0BFrcg/132"
//    ["privilege"]=> array(0) { }
//}
$user_info = $app->oauth_wechat->userinfo(); //调用接口
if(empty($user_info['nickname'])){
    return_code(2, '获取您的用户信息失败');
}

if (empty($user_info['headimgurl'])){
    $avatar = $config['default_avatar'];
}
else{
    $path = 'avatar/'.date('Y').'/'.date('md').'/'.date('H').'/';
    $avatar = $app->file->download_image($user_info['headimgurl'], $path);
    //上传到阿里云
    if (!empty($GLOBALS['config']['oss']['aliyun'])) {
        $avatar = $app->tool_oss->upload_file($project_root . 'static/' . substr($avatar, 1));
    }
}

$data = [
    'nickname' => $user_info['nickname'],
    'gender' => $user_info['sex']==1 ? 'male' : 'female',
    'birthday' => date('Y-m-d', strtotime('-18 years')),
    'avatar' => $avatar,
    'country' => $user_info['country'],
    'province' => $user_info['province'],
    'city' => $user_info['city'],
    'openid' => $openid,
    'access_token' => $token['access_token'],
    'expires_at' => time()+$token['expires_in'],
    'refresh_token' => $token['refresh_token'],
    'identity_type' => 'WX',
    'sid' => session_id(),
    'scan_sid' => session_id(),
    'ip' => client_ip(),
    'ctime' => time(),
];

//存入临时表
$data ['id'] = $app->model_try_to_sign_in->insert_table($data);
if ($is_qrcode){
    $code = $_SESSION['weixin_qr_login_code'];
    $_SESSION['weixin_qr_login_code']=null;
    $login_info = $app->redis()->get(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code);
    $login_info = json_decode($login_info, true);
    $login_info['status'] = 'login';
    $login_info = $data+$login_info;
    $app->redis()->set(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code, json_encode($login_info));
    //延长二维码的有效期
    $app->redis()->expire(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code, TIME_30_SEC);
    unset($login_info, $data, $state, $token, $user_info, $qr_login, $app);
    return_code(CODE_SUCCESS,'登录成功');
}
else {
    //存入SESSION
    $_SESSION['temp_user_info'] = json_encode($data);
    //跳转到绑定页
    header('Location: /sign/bind_account');
    unset($data, $state, $token, $user_info, $qr_login, $app);
    exit;
}