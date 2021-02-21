<?php
/**
 * @group 用户
 * @name 解除绑定QQ
 * @desc
 * @method POST
 * @uri /setting/unbind_qq
 * @param string password 登录密码 必选 经过RAS公钥加密后的密码字符串
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "解绑成功"
 * }
 * @exception
 *  0 解绑成功
 *  1 解绑失败
 *  2 密码错误
 *  3 密码错误
 *  4 您未绑定QQ账号
 */

$params = input::I()->validate(
    [
        'password' => 'required|trim|string|min:6|max:32|rsa_encrypt|return',
    ],
    [
        'password' => '密码错误',
    ],
    [
        'password' => 2,
    ]);
//检查操作权限

//检查密码是否正确
$user_info = model_user::I()->find_table(['uid'=>$self_info['uid']], '*', $self_info['uid']);
if (!$user_info || md5($params['password'].$user_info['salt'])!=$user_info['password']){
    unset($params, $user_info);
    return code(3,'密码错误');
}
unset($params, $user_info);
if(!$identity = model_user_identity::I()->find_table(['uid'=>$self_info['uid'], 'type'=>'QQ'], 'identity', $self_info['uid'])){
    unset($identity);
    return code(4,'您未绑定QQ账号');
}

if (!model_user_identity::I()->delete_identity('QQ', $identity['identity'], $self_info['uid'])){
    unset($identity);
    return code(1,'解绑失败');
}
unset($identity);
//返回结果
return json(0,'解绑成功');
