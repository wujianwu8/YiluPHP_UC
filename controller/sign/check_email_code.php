<?php
/**
 * @name 校验邮箱验证码是否正确
 * @desc 校验邮箱验证码是否正确
 * @method POST
 * @uri /sign/check_email_code
 * @param email email 地区编号 必选 加密后的邮箱地址
 * @param string verify_code 必选 验证码
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "验证码正确"
 * }
 * @exception
 *  0 验证码正确
 *  1 验证码错误
 *  2 验证码错误
 *  3 验证码错误或已失效
 *  4 该邮箱未注册账号
 *  5 该账号已经被锁住,无法找回密码
 *  11 邮箱填写有误
 */

$params = $app->input->validate(
    [
        'email' => 'required|email|min:8|max:100|rsa_encrypt|return',
        'verify_code' => 'required|string|min:6|max:6|rsa_encrypt|return',
    ],
    [
        'email.*' => $app->lang('email_error'),
        'verify_code.*' => $app->lang('verify_code_error'),
    ],
    [
        'email.*' => 1,
        'verify_code.*' => 2,
    ]);

$code_cache_key = REDIS_KEY_EMAIL_VERIFY_CODE.md5($params['email'].'_'.session_id());
//检查验证码是否正确
if(!$cache_code = $app->redis()->get($code_cache_key)){
    unset($code_cache_key, $params, $cache_code);
    return_code(3, $app->lang('verify_code_error_or_invalid'));
}
if (strtolower($cache_code)!=strtolower($params['verify_code'])){
    unset($code_cache_key, $params, $cache_code);
    return_code(4, $app->lang('verify_code_error'));
}

return_json(0, $app->lang('verify_code_correct'));