<?php
/**
 * @name 通过邮箱重设密码
 * @desc 通过邮箱重设密码
 * @method POST
 * @uri /sign/reset_password_by_email
 * @param string email 邮箱地址 必选 经过RAS公钥加密后的邮箱地址字符串
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
 *  3 该邮箱未注册账号
 *  4 该邮箱未注册账号
 *  5 该账号已经被锁住,无法找回密码
 *  11 邮箱地址有误
 *  12 密码设置不正确
 *  13 验证码错误
 */

$params = $app->input->validate(
    [
        'email' => 'required|email|min:7|max:100|rsa_encrypt|return',
        'password' => 'required|trim|string|min:6|max:3000|rsa_encrypt|return',
        'verify_code' => 'required|integer|min:1000|max:9999|return',
    ],
    [
        'email.*' => $app->lang('email_error'),
        'password.*' => $app->lang('password_too_simple'),
        'verify_code.*' => $app->lang('verify_code_error'),
    ],
    [
        'mobile.*' => 11,
        'password.*' => 12,
        'verify_code.*' => 13,
    ]);

$complete_phone = $params['area_code'].'-'.$params['mobile'];

$code_cache_key = REDIS_KEY_MOBILE_VERIFY_CODE.md5($complete_phone.'_'.session_id());
//检查验证码是否正确
if(!$cache_code = $app->redis()->get($code_cache_key)){
    unset($code_cache_key, $params, $complete_phone, $cache_code);
    return_code(1, $app->lang('verify_code_error'));
}
if (strtolower($cache_code)!=strtolower($params['verify_code'])){
    unset($code_cache_key, $params, $complete_phone, $cache_code);
    return_code(2, $app->lang('verify_code_error'));
}

//检查此手机有没有注册过
if(!$uid = $app->model_user_identity->find_uid_by_identity('INNER', $complete_phone)){
    unset($code_cache_key, $params, $complete_phone, $cache_code, $uid);
    return_code(3, $app->lang('no_account_sign_up_of_email'));
}
if(!$user_info = $app->model_user->find_table(['uid'=>$uid], '*', $uid)){
    unset($code_cache_key, $params, $complete_phone, $cache_code, $user_info);
    return_code(4, $app->lang('no_account_sign_up_of_email'));
}
if(empty($user_info['status'])) {
    unset($code_cache_key, $params, $complete_phone, $cache_code, $user_info);
    return_code(5, $app->lang('account_locked_for_reset_password'));
}

//更新用户信息
$where = ['uid' => $uid];
$data = [ 'password' => $params['password'] ];
$app->logic_user->update_user_info($where, $data);

//删除验证码
$app->redis()->del($code_cache_key);

unset($code_cache_key, $user_info, $where, $params, $complete_phone, $data, $uid);
//返回结果
return_json(0, $app->lang('set_password_successfully'));
