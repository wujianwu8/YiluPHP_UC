<?php
/**
 * @name 解除绑定支付宝账号
 * @desc
 * @method POST
 * @uri /setting/unbind_alipay
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
 *  4 您未绑定支付宝账号
 */

$params = $app->input->validate(
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
$user_info = $app->model_user->find_table(['uid'=>$self_info['uid']], '*', $self_info['uid']);
if (!$user_info || md5($params['password'].$user_info['salt'])!=$user_info['password']){
    unset($params, $user_info);
    return_code(3,'密码错误');
}
unset($params, $user_info);
if(!$identity = $app->model_user_identity->find_table(['uid'=>$self_info['uid'], 'type'=>'ALIPAY'], 'identity', $self_info['uid'])){
    unset($identity);
    return_code(4,'您未绑定支付宝账号');
}

if (!$app->model_user_identity->delete_identity('ALIPAY', $identity['identity'], $self_info['uid'])){
    unset($identity);
    return_code(1,'解绑失败');
}
unset($identity);
//返回结果
return_json(0,'解绑成功');
