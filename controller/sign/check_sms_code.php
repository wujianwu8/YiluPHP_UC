<?php
/**
 * @name 校验短信验证码是否正确
 * @desc 校验短信验证码是否正确
 * @method POST
 * @uri /sign/check_sms_code
 * @param integer area_code 地区编号 必选 5位以内的纯数字
 * @param integer mobile 手机号 必选 经过RSA公钥加密后的手机号字符串
 * @param integer verify_code 必选 手机验证码
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "验证码正确"
 * }
 * @exception
 *  0 验证码正确
 *  1 验证码错误或已失效
 *  2 验证码错误
 *  3 该手机号未注册账号
 *  4 该手机号未注册账号
 *  5 该账号已经被锁住,无法找回密码
 *  10 手机归属地有误
 *  11 手机号码填写有误
 */

$params = $app->input->validate(
    [
        'area_code' => 'required|integer|min:1|max:9999|return',
        'mobile' => 'required|integer|min:100000|max:99999999999|rsa_encrypt|return',
        'verify_code' => 'required|integer|min:1000|max:9999|rsa_encrypt|return',
    ],
    [
        'area_code.*' => $app->lang('wrong_area_code_of_mobile'),
        'mobile.*' => $app->lang('wrong_mobile_number'),
        'verify_code.*' => $app->lang('verify_code_error'),
    ],
    [
        'area_code.*' => 10,
        'mobile.*' => 11,
        'verify_code.*' => 13,
    ]);

$complete_phone = $params['area_code'].'-'.$params['mobile'];

$code_cache_key = REDIS_KEY_MOBILE_VERIFY_CODE.md5($complete_phone.'_'.session_id());
//检查验证码是否正确
if(!$cache_code = $app->redis()->get($code_cache_key)){
    unset($code_cache_key, $params, $complete_phone, $cache_code);
    return_code(1, $app->lang('verify_code_error_or_invalid'));
}
if ($cache_code!=$params['verify_code']){
    unset($code_cache_key, $params, $complete_phone, $cache_code);
    return_code(2, $app->lang('verify_code_error'));
}

//检查此手机有没有注册过
//if(!$uid = $app->model_user_identity->find_uid_by_identity('INNER', $complete_phone)){
//    unset($code_cache_key, $params, $complete_phone, $cache_code, $uid);
//    return_code(3,'此手机号未注册');
//}
//if(!$user_info = $app->model_user->find_table(['uid'=>$uid], '*', $uid)){
//    unset($code_cache_key, $params, $complete_phone, $cache_code, $user_info);
//    return_code(4,'此手机号未注册');
//}
//if(empty($user_info['status'])) {
//    unset($code_cache_key, $params, $complete_phone, $cache_code, $user_info);
//    return_code(5, '该账号已经被锁住,无法找回密码');
//}

return_json(0, $app->lang('verify_code_correct'));