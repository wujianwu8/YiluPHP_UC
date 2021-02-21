<?php
/**
 * @group 用户
 * @name 通过手机号重设密码
 * @desc 通过手机号重设密码
 * @method POST
 * @uri /sign/reset_password_by_mobile
 * @param integer area_code 地区编号 必选 5位以内的纯数字
 * @param integer mobile 手机号 必选 经过RAS公钥加密后的手机号字符串
 * @param string password 密码 必选 经过RAS公钥加密后的密码字符串
 * @param integer verify_code 必选 手机验证码
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "设置密码成功"
 * }
 * @exception
 *  0 设置密码成功
 *  1 验证码错误
 *  2 验证码错误
 *  3 该手机号未注册账号
 *  4 该手机号未注册账号
 *  5 该账号已经被锁住,无法找回密码
 *  10 手机归属地有误
 *  11 手机号码填写有误
 *  12 密码设置不正确
 *  13 验证码错误
 */

$params = input::I()->validate(
    [
        'area_code' => 'required|integer|min:1|max:9999|return',
        'mobile' => 'required|integer|min:100000|max:99999999999|rsa_encrypt|return',
        'password' => 'required|trim|string|min:6|max:3000|rsa_encrypt|return',
        'verify_code' => 'required|integer|min:1000|max:9999|return',
    ],
    [
        'area_code.*' => YiluPHP::I()->lang('wrong_area_code_of_mobile'),
        'mobile.*' => YiluPHP::I()->lang('wrong_mobile_number'),
        'password.*' => YiluPHP::I()->lang('password_too_simple'),
        'verify_code.*' => YiluPHP::I()->lang('verify_code_error'),
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
if(!$cache_code = redis_y::I()->get($code_cache_key)){
    unset($code_cache_key, $params, $complete_phone, $cache_code);
    return code(1, YiluPHP::I()->lang('verify_code_error'));
}
if ($cache_code!=$params['verify_code']){
    unset($code_cache_key, $params, $complete_phone, $cache_code);
    return code(2, YiluPHP::I()->lang('verify_code_error'));
}

//检查此手机有没有注册过
if(!$uid = model_user_identity::I()->find_uid_by_identity('INNER', $complete_phone)){
    unset($code_cache_key, $params, $complete_phone, $cache_code, $uid);
    return code(3, YiluPHP::I()->lang('no_account_sign_up_of_mobile'));
}
if(!$user_info = model_user::I()->find_table(['uid'=>$uid], '*', $uid)){
    unset($code_cache_key, $params, $complete_phone, $cache_code, $user_info);
    return code(4, YiluPHP::I()->lang('no_account_sign_up_of_mobile'));
}
if(empty($user_info['status'])) {
    unset($code_cache_key, $params, $complete_phone, $cache_code, $user_info);
    return code(5, YiluPHP::I()->lang('account_locked_for_reset_password'));
}

//更新用户信息
$where = ['uid' => $uid];
$data = [ 'password' => $params['password'] ];
logic_user::I()->update_user_info($where, $data);

//删除验证码
redis_y::I()->del($code_cache_key);

unset($code_cache_key, $user_info, $where, $params, $complete_phone, $data, $uid);
//返回结果
return json(0, YiluPHP::I()->lang('set_password_successfully'));
