<?php
/**
 * @group 用户
 * @name 绑定或更换登录手机
 * @desc 已登录用户绑定或更换登录手机号。已设置登录密码的账号必须验证现有密码；未设置密码的第三方授权账号需在绑定时设置登录密码。操作成功后保留当前会话，并使其它登录会话失效。
 * @method POST
 * @uri /setting/save_mobile
 * @param integer area_code 手机地区编号 必选 1-9999以内的数字
 * @param integer mobile 新手机号 必选 经过RSA公钥加密后的手机号字符串
 * @param integer verify_code 短信验证码 必选 通过send_sms_code接口以bind_account用途发送
 * @param string password 现有登录密码 条件必选 已设置登录密码时必填，经过RSA公钥加密
 * @param string new_password 新登录密码 条件必选 未设置登录密码时必填，经过RSA公钥加密
 * @param string confirm_password 确认新登录密码 条件必选 未设置登录密码时必填，经过RSA公钥加密
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "手机绑定成功"
 * }
 * @exception
 *  0 手机绑定或更换成功
 *  3 验证码错误或已失效
 *  4 用户不存在
 *  5 登录密码错误
 *  6 两次输入的新密码不一致
 *  7 新登录密码不符合安全规则
 *  8 新手机号与当前绑定手机号相同
 *  9 新手机号已经被其它账号使用
 *  10 数据库操作失败，请稍后重试
 *  100 参数缺失、格式错误或RSA解密失败
 */

$params = input::I()->validate(
    [
        'area_code' => 'required|integer|min:1|max:9999|return',
        'mobile' => 'required|integer|min:100000|max:99999999999|rsa_encrypt|return',
        'verify_code' => 'required|integer|min:1000|max:9999|return',
        'password' => 'string|min:6|max:3000|rsa_encrypt|return',
        'new_password' => 'string|min:6|max:3000|rsa_encrypt|return',
        'confirm_password' => 'string|min:6|max:3000|rsa_encrypt|return',
    ],
    [
        'area_code.*' => YiluPHP::I()->lang('wrong_area_code_of_mobile'),
        'mobile.*' => YiluPHP::I()->lang('wrong_mobile_number'),
        'verify_code.*' => YiluPHP::I()->lang('verify_code_error'),
        'password.*' => YiluPHP::I()->lang('login_password_error'),
        'new_password.*' => YiluPHP::I()->lang('password_too_simple'),
        'confirm_password.*' => YiluPHP::I()->lang('re_input_password_error'),
    ]
);

$uid = $self_info['uid'];
$phone = $params['area_code'].'-'.$params['mobile'];
$code_cache_key = REDIS_KEY_MOBILE_VERIFY_CODE.md5($phone.'_'.session_id());
$cache_code = redis_y::I()->get($code_cache_key);
if (!$cache_code || strval($cache_code) !== strval($params['verify_code'])) {
    return code(3, YiluPHP::I()->lang('verify_code_error_or_invalid'));
}

$user_info = model_user::I()->find_table(['uid'=>$uid], '*', $uid);
if (!$user_info) {
    return code(4, YiluPHP::I()->lang('user_not_exist'));
}
$has_password = !empty($user_info['password']) && !empty($user_info['salt']);
if ($has_password) {
    if (empty($params['password']) || md5($params['password'].$user_info['salt']) !== $user_info['password']) {
        return code(5, YiluPHP::I()->lang('login_password_error'));
    }
}
else {
    if (empty($params['new_password']) || empty($params['confirm_password']) ||
        $params['new_password'] !== $params['confirm_password']) {
        return code(6, YiluPHP::I()->lang('re_input_password_error'));
    }
    if (!is_safe_password($params['new_password'])) {
        return code(7, YiluPHP::I()->lang('password_too_simple'));
    }
}

$old_mobile = logic_user::I()->get_mobile_by_uid($uid);
if ($old_mobile === $phone) {
    return code(8, YiluPHP::I()->lang('mobile_same_as_current'));
}
if ($owner_uid = model_user_identity::I()->find_uid_by_identity('INNER', $phone)) {
    return code(9, YiluPHP::I()->lang('mobile_used_by_other_account'));
}

$connections = array_values(array_unique([
    model_user::I()->sub_connection($uid),
    model_user_identity::I()->sub_connection($uid),
]));
$new_identity_inserted = false;
$old_identity_deleted = false;
try {
    foreach ($connections as $connection) {
        mysql::I($connection)->beginTransaction();
    }

    model_user_identity::I()->insert_identity([
        'uid' => $uid,
        'type' => 'INNER',
        'identity' => $phone,
        'ctime' => time(),
    ]);
    $new_identity_inserted = true;
    if ($old_mobile !== '') {
        model_user_identity::I()->delete_identity('INNER', $old_mobile, $uid);
        $old_identity_deleted = true;
    }
    if (!$has_password) {
        $where = ['uid'=>$uid];
        logic_user::I()->update_user_info($where, ['password'=>$params['new_password']]);
    }

    foreach ($connections as $connection) {
        mysql::I($connection)->commit();
    }
}
catch (Exception $e) {
    foreach ($connections as $connection) {
        if (mysql::I($connection)->inTransaction()) {
            mysql::I($connection)->rollBack();
        }
    }
    if ($new_identity_inserted) {
        model_user_identity::I()->delete_user_identity_cache('INNER', $phone, $uid);
    }
    if ($old_identity_deleted) {
        model_user_identity::I()->cache_user_identity('INNER', $old_mobile, $uid);
    }
    write_applog('ERROR', '绑定手机失败 uid='.$uid.', message='.$e->getMessage());
    return code(10, YiluPHP::I()->lang('binding_failed_retry'));
}

redis_y::I()->del($code_cache_key);
logic_user::I()->revoke_other_login_sessions($uid);
return json(CODE_SUCCESS, YiluPHP::I()->lang($old_mobile === '' ? 'mobile_bind_success' : 'mobile_change_success'));
