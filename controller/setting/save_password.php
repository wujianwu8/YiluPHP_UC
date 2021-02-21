<?php
/**
 * @group 用户
 * @name 保存新密码
 * @desc
 * @method POST
 * @uri /setting/save_password
 * @param string password 登录密码 必选 经过RAS公钥加密后的密码字符串
 * @param string current_password 现用密码 必选 经过RAS公钥加密后的密码字符串
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "修改成功"
 * }
 * @exception
 *  0 修改成功
 *  1 修改失败
 *  2 密码错误
 *  3 现用密码错误
 *  4 新密码太简单,密码长度需为6-20位,且同时包含大小写字母,数字和@#$!_-中的一个符号
 *  5 现用密码错误
 *  6 现用密码错误
 *  7 新密码与现用密码一样，无需修改
 */

$params = input::I()->validate(
    [
        'password' => 'required|trim|string|min:6|max:20|rsa_encrypt|return',
        'current_password' => 'required|trim|string|min:6|max:20|rsa_encrypt|return',
    ],
    [
        'password' => '密码错误',
        'current_password' => '现用密码错误',
    ],
    [
        'password' => 2,
        'current_password' => 3,
    ]);
//检查操作权限

if (!is_safe_password($params['password'])){
    unset($params);
    return code(4, '新密码太简单,密码长度需为6-20位,且同时包含大小写字母,数字和@#$!_-中的一个符号');
}
if (!is_safe_password($params['current_password'])){
    unset($params);
    return code(5, '现用密码错误');
}

//检查现用密码是否正确
$user_info = model_user::I()->find_table(['uid'=>$self_info['uid']], '*', $self_info['uid']);
if (!$user_info || md5($params['current_password'].$user_info['salt'])!=$user_info['password']){
    unset($params, $user_info);
    return code(6,'现用密码错误');
}

if (md5($params['password'].$user_info['salt'])==$user_info['password']){
    unset($params, $user_info);
    return code(7, '新密码与现用密码一样，无需修改');
}

$where = [
    'uid'=>$self_info['uid'],
];
$data = [
    'password'=>$params['password'],
];
if(!logic_user::I()->update_user_info($where, $data)){
    unset($params, $user_info);
    return json(1,'修改失败');
}
logic_user::I()->destroy_login_session();

unset($params, $user_info);
//返回结果
return json(CODE_SUCCESS,'修改成功');
