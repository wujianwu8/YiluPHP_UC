<?php
/**
 * @group 用户
 * @name 找回用户密码
 * @desc 找回用户密码
 * @method POST
 * @uri /sign/reset_password
 * @param integer email 邮箱地址 可选 通过邮箱找回密码时必选
 * @param integer mobile 手机号 可选 通过手机号找回密码时必选,格式如:86-13612341234
 * @param string password 密码 必选 经过RSA公钥加密后的密码字符串
 * @param string verify_code 必选 手机/邮箱验证码
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "设置密码成功"
 * }
 * @exception
 *  0 注册成功
 *  1 邮箱地址填写有误
 *  2 手机号码填写有误
 *  3 密码设置不正确
 *  4 验证码错误
 *  5 手机或邮箱,必须选择一个用于找回密码
 *  6 密码太简单,密码长度需为6-20位,且同时包含大小写字母,数字和@#$!_-中的一个符号
 *  7 验证码错误或已失效[手机验证码]
 *  8 验证码错误或已失效[邮箱验证码]
 *  9 验证码错误
 *  10 账号不存在[缓存中没找到用户ID]
 *  11 账号不存在[用户表中没有找到用户信息]
 *  12 该账号已经被锁住,无法找回密码
 *  13
 */

$params = input::I()->validate(
    [
        'email' => 'string|trim|min:8|max:100|rsa_encrypt|return',
        'mobile' => 'string|trim|min:8|max:30|rsa_encrypt|return',
        'password' => 'required|trim|string|min:6|max:20|rsa_encrypt|return',
        'verify_code' => 'required|trim|string|min:4|max:6|rsa_encrypt|return',
    ],
    [
        'email.*' => YiluPHP::I()->lang('email_error'),
        'mobile.*' => YiluPHP::I()->lang('wrong_mobile_number'),
        'password.*' => YiluPHP::I()->lang('password_too_simple'),
        'verify_code.*' => YiluPHP::I()->lang('verify_code_error'),
    ],
    [
        'email.*' => 1,
        'mobile.*' => 2,
        'password.*' => 3,
        'verify_code.*' => 4,
    ]);

if(empty($params['email']) && empty($params['mobile'])){
    unset($params);
    return code(5, YiluPHP::I()->lang('mobile_or_email'));
}

if(!is_safe_password($params['password'])){
    unset($params);
    return code(6, YiluPHP::I()->lang('password_too_simple'));
}

if(!empty($params['mobile'])) {
    $code_cache_key = REDIS_KEY_MOBILE_VERIFY_CODE . md5($params['mobile'] . '_' . session_id());
    //检查验证码是否正确
    if (!$cache_code = redis_y::I()->get($code_cache_key)) {
        unset($code_cache_key, $params, $complete_phone, $cache_code);
        return code(7,  YiluPHP::I()->lang('verify_code_error_or_invalid'));
    }
    $account = $params['mobile'];
}
else if(!empty($params['email'])) {
    $code_cache_key = REDIS_KEY_EMAIL_VERIFY_CODE.md5($params['email'].'_'.session_id());
    //检查验证码是否正确
    if(!$cache_code = redis_y::I()->get($code_cache_key)){
        unset($code_cache_key, $params, $cache_code);
        return code(8, YiluPHP::I()->lang('verify_code_error_or_invalid'));
    }
    $account = $params['email'];
}
if (strtolower($cache_code) != strtolower($params['verify_code'])) {
    unset($code_cache_key, $params, $account, $cache_code);
    return code(9, YiluPHP::I()->lang('verify_code_error'));
}

//检查此手机/邮箱有没有注册过
if(!$uid = model_user_identity::I()->find_uid_by_identity('INNER', $account)){
    unset($code_cache_key, $params, $account, $cache_code, $uid);
    return code(10, YiluPHP::I()->lang('login_account_does_not_exist'));
}
if(!$user_info = model_user::I()->find_table(['uid'=>$uid], 'status', $uid)){
    unset($code_cache_key, $params, $account, $cache_code, $uid, $user_info);
    return code(11, YiluPHP::I()->lang('login_account_does_not_exist'));
}
if(empty($user_info['status'])) {
    unset($code_cache_key, $params, $account, $cache_code, $uid, $user_info);
    return code(12, YiluPHP::I()->lang('account_locked_for_reset_password'));
}

$where = ['uid'=>$uid];
logic_user::I()->update_user_info($where, ['password' => $params['password']]);

//删除验证码
redis_y::I()->del($code_cache_key);

unset($code_cache_key, $params, $account, $cache_code, $uid, $user_info, $where);
//返回结果
return json(0, YiluPHP::I()->lang('set_password_successfully'));
