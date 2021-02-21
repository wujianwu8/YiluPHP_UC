<?php
/**
 * @group 用户
 * @name Linkedin授权登录的回调地址
 * @desc
 * @method GET
 * @uri /sign/linkedin_callback
 * @param string code 授权码   必选    详细参考Linkedin的授权文档
 * @param string state 状态码  必选   用于防止CSRF攻击的字符串，详细参考Linkedin的授权文档
 * @return 跳转页面或显示“登录成功”
 * @exception   会在界面上显示错误
 */


oauth_linkedin::I()->check_callback();
$token = oauth_linkedin::I()->get_access_token();
if(!$token || isset($token['error'])){
    return code(CODE_UNDEFINED_ERROR_TYPE,'Linkedin授权登录失败。'.(isset($token['error'])?'. '.$token['error'].'：'.$token['error']:''));
}

oauth_linkedin::I()->set_access_token($token['access_token']);
$user_info = oauth_linkedin::I()->check_access_token();
if (empty($user_info) || !isset($user_info['id']) || !isset($user_info['first-name'])) {
    return code(CODE_UNDEFINED_ERROR_TYPE,'Linkedin授权登录失败。'.(is_array($user_info)?json_encode($user_info):''));
}


//如果用户已经创建，则直接跳转
//return code(CODE_UNDEFINED_ERROR_TYPE,'登录成功');

/*
array(5) {
  ["id"]=>
  string(10) "CI265Mhdch"
  ["first-name"]=>
  string(3) "航"
  ["last-name"]=>
  string(3) "叶"
  ["headline"]=>
  string(42) "Paramida Tech Ltd - Director Of Operations"
  ["site-standard-profile-request"]=>
  array(1) {
    ["url"]=>
    string(136) "https://www.linkedin.com/profile/view?id=AAoAACsSTXEB0E0XK-iXqgvzG5ycsyNSPnu7QXE&authType=name&authToken=YbxM&trk=api*a5451705*s5764065*"
  }
}
*/

$data = [
    'nickname' => $user_info['last-name'].$user_info['first-name'],
    'gender' => 'female',
    'birthday' => date('Y-m-d', strtotime('-18 years')),
    'avatar' => '',
    'country' => '',
    'province' => '',
    'city' => '',
    'openid' => $user_info['id'],
    'access_token' => $token['access_token'],
    'expires_at' => time()+$token['expires_in'],
    'refresh_token' => '',
    'identity_type' => 'LINKEDIN',
    'sid' => session_id(),
    'scan_sid' => session_id(),
    'ip' => client_ip(),
    'ctime' => time(),
];

//存入临时表
$data ['id'] = insert_table($data, 'try_to_sign_in');

//存入SESSION
$_SESSION['temp_user_info'] = json_encode($data);
//跳转到绑定页
header('Location: /sign/bind_account');
unset($data, $token, $user_info, $app);
exit;
