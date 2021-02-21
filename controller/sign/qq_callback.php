<?php
/**
 * @group 用户
 * @name QQ授权登录后的回调地址
 * @desc
 * @method GET
 * @uri /sign/qq_callback
 * @param string code 授权码   必选    详细参考微信公众平台（服务号）授权登录的文档
 * @param string state 状态码  必选   用于防止CSRF攻击的字符串，详细参考微信公众平台（服务号）授权登录的文档
 * @return 跳转页面或显示“登录成功”
 * @exception   会在界面上显示错误
 */

//string(14) "$access_token="
//array(4) {
//    ["access_token"]=>
//  string(32) "4F3B47BDB096CF27AECA3AF98AF3ED62"
//    ["expires_in"]=>
//  string(7) "7776000"
//    ["refresh_token"]=>
//  string(32) "5F2D0AA872038DAE7FC35EE4C0C3EA31"
//    ["openid"]=>
//  string(32) "CD4C576D9E3BE2755E731D1A86BA0581"
//}

oauth_qq::I()->check_callback();
$token = oauth_qq::I()->get_access_token();

//如果是登录用户绑定第三方账号，走此流程
if (input::I()->get_int('for_bind', null)){
    logic_user::I()->bind_outer_account('QQ', $token['openid']);
}

//如果用户已经注册,则直接跳转
if($uid = model_user_identity::I()->find_uid_by_identity('QQ', $token['openid'])) {
    //登录用户
    $user_info = logic_user::I()->login_by_uid($uid);
    $redirect_uri = logic_user::I()->auto_jump(true, $user_info['tlt']);

    echo '<script>
        window.opener.location.href = "'.$redirect_uri.'";
        window.close();
    </script>';
    exit;
}

oauth_qq::I()->set_access_token($token['access_token']);
oauth_qq::I()->set_openid($token['openid']);

//array (size=19)
//  'ret' => int 0
//  'msg' => string '' (length=0)
//  'is_lost' => int 0
//  'nickname' => string 'Jim.Wu' (length=6)
//  'gender' => string '男' (length=3)
//  'province' => string '广东' (length=6)
//  'city' => string '深圳' (length=6)
//  'year' => string '1984' (length=4)
//  'constellation' => string '' (length=0)
//  'figureurl' => string 'http://qzapp.qlogo.cn/qzapp/101150055/CD4C576D9E3BE2755E731D1A86BA0581/30' (length=73)
//  'figureurl_1' => string 'http://qzapp.qlogo.cn/qzapp/101150055/CD4C576D9E3BE2755E731D1A86BA0581/50' (length=73)
//  'figureurl_2' => string 'http://qzapp.qlogo.cn/qzapp/101150055/CD4C576D9E3BE2755E731D1A86BA0581/100' (length=74)
//  'figureurl_qq_1' => string 'http://thirdqq.qlogo.cn/qqapp/101150055/CD4C576D9E3BE2755E731D1A86BA0581/40' (length=75)
//  'figureurl_qq_2' => string 'http://thirdqq.qlogo.cn/qqapp/101150055/CD4C576D9E3BE2755E731D1A86BA0581/100' (length=76)
//  'is_yellow_vip' => string '0' (length=1)
//  'vip' => string '0' (length=1)
//  'yellow_vip_level' => string '0' (length=1)
//  'level' => string '0' (length=1)
//  'is_yellow_year_vip' => string '0' (length=1)

//if(!$user_info = qq_connect::I()->get_user_info()){
//    return code(2, '获取您的用户信息失败');
//}
$user_info = oauth_qq::I()->get_user_info(); //调用接口

if(empty($user_info['nickname'])){
    return code(2, '获取您的用户信息失败');
}

$avatar = empty($user_info['figureurl_qq']) ? (empty($user_info['figureurl_2'])?'':$user_info['figureurl_2']) : $user_info['figureurl_qq'];
if (empty($avatar)){
    $avatar = $config['default_avatar'];
}
else{
    $path = 'avatar/'.date('Y').'/'.date('md').'/'.date('H').'/';
    $avatar = file::I()->download_image($avatar, $path);
    //上传到阿里云
    if (!empty($GLOBALS['config']['oss']['aliyun'])) {
        $avatar = tool_oss::I()->upload_file(APP_PATH . 'static/' . substr($avatar, 1));
    }
}

$data = [
    'nickname' => $user_info['nickname'],
    'gender' => $user_info['gender']=='男' ? 'male' : 'female',
    'birthday' => date('Y-m-d', strtotime($user_info['year'])),
    'avatar' => $avatar,
    'country' => '',
    'province' => $user_info['province'],
    'city' => $user_info['city'],
    'openid' => $token['openid'],
    'access_token' => $token['access_token'],
    'expires_at' => time()+$token['expires_in'],
    'refresh_token' => $token['refresh_token'],
    'identity_type' => 'QQ',
    'sid' => session_id(),
    'ip' => client_ip(),
    'ctime' => time(),
];

//存入临时表
$data ['id'] = model_try_to_sign_in::I()->insert_table($data);
//存入SESSION
$_SESSION['temp_user_info'] = json_encode($data);
unset($data, $token, $avatar, $user_info, $app);
//跳转到绑定页
header('Location: /sign/bind_account');
exit;