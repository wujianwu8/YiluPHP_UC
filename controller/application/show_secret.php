<?php
/**
 * @group 应用系统
 * @name 显示应用密匙
 * @desc
 * @method POST
 * @uri /application/show_secret
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
 */

if (!$app->logic_permission->check_permission('user_center:view_app_sceret')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
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
    return_code(3, '登录密码错误');
}

//检查现用密码是否正确
$user_info = $app->model_user->find_table(['uid'=>$self_info['uid']], '*', $self_info['uid']);
if (!$user_info || md5($params['password'].$user_info['salt'])!=$user_info['password']){
    unset($params, $user_info);
    return_code(4,'登录密码错误');
}
unset($user_info);

if (!$info=$app->model_application->find_table(['app_id' => $params['app_id']], 'app_secret')){
    unset($params, $info);
    return_code(5,'应用不存在');
}

unset($params);
//返回结果
return_json(0,'操作成功', ['app_secret'=>$info['app_secret']]);
