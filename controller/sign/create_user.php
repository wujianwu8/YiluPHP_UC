<?php
/**
 * @group 用户
 * @name 注册一个新用户
 * @desc 如果是从第三方授权登录过来的，则会登录后绑定
 * @method POST
 * @uri /sign/create_user
 * @param integer area_code 地区编号 必选 5位以内的纯数字
 * @param integer mobile 手机号 必选 经过RSA公钥加密后的手机号字符串
 * @param string password 密码 必选 经过RSA公钥加密后的密码字符串
 * @param integer verify_code 必选 手机验证码
 * @param integer is_bind 是否绑定第三方 可选 0表示不用绑定第三方账号，1表示需要绑定第三方账号（即通过授权登录的用户），默认为0
 * @return json
 * {
 *      code: 0
 *      ,data: [
 *          'tlt':'fsdfsdfkhwifsfsdsss' //临时登录令牌tlt
 *      ]
 *      ,msg: "注册成功"
 * }
 * @exception
 *  0 注册成功
 *  1 请先发送手机验证码
 *  2 手机号码填写有误
 *  3 验证码错误
 *  4 此手机号已注册
 *  5 授权登录已失效，请重新授权登录
 *  6 授权登录已失效，请重新授权登录
 *  7 密码太简单,密码长度需为6-20位,且同时包含大小写字母,数字和@#$!_-中的一个符号
 *  10 手机归属地有误
 *  11 手机号码填写有误
 *  12 密码设置不正确
 *  13 验证码错误
 */

$params = $app->input->validate(
    [
        'area_code' => 'required|integer|min:1|max:9999|return',
        'mobile' => 'required|integer|min:100000|max:99999999999|rsa_encrypt|return',
        'password' => 'required|trim|string|min:6|max:3000|rsa_encrypt|return',
        'verify_code' => 'required|integer|min:1000|max:9999|return',
    ],
    [
        'area_code.*' => $app->lang('wrong_area_code_of_mobile'),
        'mobile.*' => $app->lang('wrong_mobile_number'),
        'password.*' => $app->lang('password_too_simple'),
        'verify_code.*' => $app->lang('verify_code_error'),
    ],
    [
        'area_code.*' => 10,
        'mobile.*' => 11,
        'password.*' => 12,
        'verify_code.*' => 13,
    ]);

$complete_phone = $params['area_code'].'-'.$params['mobile'];

$code_cache_key = REDIS_KEY_MOBILE_VERIFY_CODE.md5($complete_phone.'_'.session_id());
//检查验证码是否正确
if(!$cache_code = $app->redis()->get($code_cache_key)){
    unset($code_cache_key, $params, $complete_phone, $cache_code);
    return_code(2,'请先发送手机验证码');
}
if ($cache_code!=$params['verify_code']){
    unset($code_cache_key, $params, $complete_phone, $cache_code);
    return_code(3, $app->lang('verify_code_error'));
}

if(!is_safe_password($params['password'])){
    unset($params);
    return_code(7, $app->lang('password_too_simple'));
}

//检查此手机有没有注册过
if($uid = $app->model_user_identity->find_uid_by_identity('INNER', $complete_phone)){
    unset($code_cache_key, $params, $complete_phone, $cache_code, $uid);
    return_code(4, $app->lang('mobile_is_signed_up'));
}
if(!$uid = $app->uuid->newUserId()){
    unset($code_cache_key, $params, $complete_phone, $cache_code, $uid);
    return_code(1, $app->lang('failed_to_create_uid'));
}

$time = time();
$user_info = [
    'uid' => $uid,
    'password' => $params['password'],
    'salt' => uniqid(),
    'mtime' => $time,
    'ctime' => $time,

    'type' => 'INNER',
    'identity' => $complete_phone,
    'ctime' => $time,
];

$nickname = '';
//查看SESSION中是否有从第三方登录过来的使用信息
//用于判断是否要绑定账号
$is_bind = $app->input->post_int('is_bind',0);
if($is_bind===1){
    if(!isset($_SESSION['temp_user_info'])){
        unset($code_cache_key, $params, $complete_phone, $cache_code, $uid, $user_info, $is_bind, $time);
        return_code(5, $app->lang('authorized_login_has_expired'));
    }
    $temp_user_info = $_SESSION['temp_user_info'];
    $temp_user_info = json_decode($temp_user_info, true);
    if(empty($temp_user_info['identity_type'])){
        unset($code_cache_key, $params, $complete_phone, $cache_code, $uid, $user_info, $is_bind, $temp_user_info, $time);
        return_code(6, $app->lang('authorized_login_has_expired'));
    }

    //去掉前后的空格
    $nickname = trim($temp_user_info['nickname']);
    //连续多个空格只留一个
    $nickname = preg_replace('/[ \f\n\r\t]+/',' ', $nickname);

    $identity_plat = [
        'uid' => $uid,
        'type' => $temp_user_info['identity_type'],
        'identity' => $temp_user_info['openid'],
        'ctime' => $time,
    ];
    isset($temp_user_info['access_token']) && $identity_plat['access_token']=$temp_user_info['access_token'];
    isset($temp_user_info['expires_at']) && $identity_plat['expires_at']=$temp_user_info['expires_at'];
    isset($temp_user_info['refresh_token']) && $identity_plat['refresh_token']=$temp_user_info['refresh_token'];
    isset($temp_user_info['gender']) && $user_info['gender'] = $temp_user_info['gender'];
    isset($temp_user_info['birthday']) && $user_info['birthday'] = $temp_user_info['birthday'];
    isset($temp_user_info['avatar']) && $user_info['avatar'] = $temp_user_info['avatar'];
    isset($temp_user_info['country']) && $user_info['country'] = $temp_user_info['country'];
    isset($temp_user_info['province']) && $user_info['province'] = $temp_user_info['province'];
    isset($temp_user_info['city']) && $user_info['city'] = $temp_user_info['city'];
    if (isset($user_info['country'])){
        $user_info['country'] = $app->lib_address->getCountryLangKey($user_info['country']);
    }
}
//如果没有昵称，则生成一个昵称
if(!$nickname){
    $nickname = $app->model_user->mobile_to_nickname($params['mobile']);
}
//保证昵称的唯一性
$user_info['nickname'] = $app->model_user->get_an_available_nickname($nickname);

//保存入库
$app->logic_user->create_user($user_info);
if($is_bind===1){
    $GLOBALS['app']->model_user_identity->insert_table($identity_plat);
    //把登录身份存入缓存
    $GLOBALS['app']->model_user_identity->cache_user_identity($identity_plat['type'], $identity_plat['identity'], $identity_plat['uid']);
}

//删除验证码
$app->redis()->del($code_cache_key);

//登录用户
$app->logic_user->create_login_session($user_info);
$tlt = $user_info['tlt'];

unset($code_cache_key, $user_info, $is_bind, $params, $complete_phone, $cache_code, $uid, $nickname, $time);
//返回结果
return_json(0, $app->lang('sign_up_successful'), ['tlt' => $tlt]);
