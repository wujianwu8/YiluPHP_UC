<?php
/**
 * @group 应用系统
 * @name 重新生成应用密匙
 * @desc
 * @method POST
 * @uri /application/refresh_secret
 * @param string app_id 应用ID 必选
 * @param string password 登录密码 必选 经过RAS公钥加密后的密码字符串
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "操作成功"
 * }
 * @exception
 *  0 操作成功
 *  1 应用ID错误
 *  2 登录密码错误
 *  3 登录密码错误
 *  4 登录密码错误
 *  5 应用不存在
 *  6 操作失败
 */

if (!logic_permission::I()->check_permission('user_center:refresh_app_sceret')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'app_id' => 'required|trim|string|min:3|max:20|return',
        'password' => 'required|trim|string|min:6|max:32|rsa_encrypt|return',
    ],
    [
        'app_id.*' => '应用ID错误',
        'password.*' => '登录密码错误',
    ],
    [
        'app_id.*' => 1,
        'password.*' => 2,
    ]);

if (!is_safe_password($params['password'])){
    unset($params);
    return code(3, '登录密码错误');
}

//检查现用密码是否正确
$user_info = model_user::I()->find_table(['uid'=>$self_info['uid']], '*', $self_info['uid']);
if (!$user_info || md5($params['password'].$user_info['salt'])!=$user_info['password']){
    unset($params, $user_info);
    return code(4,'登录密码错误');
}
unset($user_info);

if (!$info=model_application::I()->find_table(['app_id' => $params['app_id']], 'app_secret')){
    unset($params, $info);
    return code(5,'应用不存在');
}
unset($info);

$where = [
    'app_id' => $params['app_id'],
];
$data = [
    'app_secret' => md5($self_info['uid'].uniqid().microtime().uniqid()),
];
if(model_application::I()->update_table($where, $data)){
    unset($params, $where);
    //返回结果
    return json(0,'操作成功', ['app_secret'=>$data['app_secret']]);
}
unset($params, $where, $data);
return code(6,'操作失败');