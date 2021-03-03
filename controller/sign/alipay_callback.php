<?php
/**
 * @group 用户
 * @name 支付宝授权登录的回调地址
 * @desc
 * @method GET
 * @uri /sign/alipay_callback
 * @param string auth_code 授权码   必选    用此code换取access_token
 * @param string state 状态码  必选   用于防止CSRF攻击的字符串，详细参考微信公众平台（服务号）授权登录的文档
 * @return 跳转页面或显示“登录成功”
 * @exception   会在界面上显示错误
 */

if(!$auth_code = input::I()->get_trim('auth_code')){
    return code(CODE_UNDEFINED_ERROR_TYPE,'缺少参数：auth_code');
}
/* *
object(stdClass)#16 (6) {
  ["access_token"]=> string(40) "authusrBa3e124c841983j47881d662360e9fX75"
  ["alipay_user_id"]=> string(32) "20881072459123186845340412347575"
  ["expires_in"]=> int(1296000)
  ["re_expires_in"]=> int(2592000)
  ["refresh_token"]=> string(40) "authusrB0b7489376f9d420cb4008c210bfbdX75"
  ["user_id"]=> string(16) "2088102598764752"
}
 * */
$access_token = oauth_alipay::I()->get_access_token($auth_code);

//如果是登录用户绑定第三方账号，走此流程
if (input::I()->get_int('for_bind', null)){
    logic_user::I()->bind_outer_account('ALIPAY', $access_token->user_id);
}
//如果用户已经注册,则直接跳转
if($uid = model_user_identity::I()->find_uid_by_identity('ALIPAY', $access_token->user_id)) {
    //登录用户
    $user_info = logic_user::I()->login_by_uid($uid);
    logic_user::I()->auto_jump(false, $user_info['tlt']);
}

/* *
    object(stdClass)#20 (2) {
      ["alipay_user_info_share_response"]=>
      object(stdClass)#19 (12) {
        ["code"]=>string(5) "10000"
        ["msg"]=> string(7) "Success"
        ["avatar"]=> string(63) "https://tfs.alipayobjects.com/images/partner/T11TjkoKhyXXXXXXXX"
        ["city"]=> string(9) "深圳市"
        ["gender"]=> string(1) "m"
        ["is_certified"]=> string(1) "T"
        ["is_student_certified"]=> string(1) "F"
        ["nick_name"]=> string(9) "Jim.Wu"
        ["province"]=> string(9) "广东省"
        ["user_id"]=> string(16) "2088102598764752"
        ["user_status"]=> string(1) "T"
        ["user_type"]=> string(1) "2"
      }
      ["sign"]=> string(344) "PbTl0q4wWlk...gtZ0wxISEPXNItwQ=="
    }
 * */
$user_info = oauth_alipay::I()->get_user_info($access_token->access_token);

if (empty($user_info->avatar)){
    $avatar = $config['default_avatar'];
}
else{
    $path = 'avatar/'.date('Y').'/'.date('md').'/'.date('H').'/';
    $avatar = file::I()->download_image($user_info->avatar, $path);
    //上传到阿里云
    if (!empty($GLOBALS['config']['oss']['aliyun']['enable'])) {
        $avatar = tool_oss::I()->upload_file(APP_PATH . 'static/' . substr($avatar, 1));
    }
}

$data = [
    'nickname' => $user_info->nick_name,
    'gender' => $user_info->gender=='m' ? 'male' : 'female',
    'birthday' => date('Y-m-d', strtotime('-18 years')),
    'avatar' => $avatar,
    'country' => '中国',
    'province' => $user_info->province,
    'city' => $user_info->city,
    'openid' => $user_info->user_id,
    'access_token' => $access_token->access_token,
    'expires_at' => time()+$access_token->expires_in,
    'refresh_token' => $access_token->refresh_token,
    'identity_type' => 'ALIPAY',
    'sid' => session_id(),
    'scan_sid' => session_id(),
    'ip' => client_ip(),
    'ctime' => time(),
];

//存入临时表
$data ['id'] = model_try_to_sign_in::I()->insert_table($data);

//存入SESSION
$_SESSION['temp_user_info'] = json_encode($data);
//跳转到绑定页
header('Location: /sign/bind_account');
unset($data, $auth_code, $access_token, $user_info, $avatar, $app);
exit;