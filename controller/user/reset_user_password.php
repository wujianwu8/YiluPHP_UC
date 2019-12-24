<?php
/**
 * @name 重置用户密码
 * @desc
 * @method POST
 * @uri /user/reset_user_password
 * @param integer uid 用户ID 必选
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "重置密码成功"
 * }
 * @exception
 *  0 重置密码成功
 *  1 用户ID参数错误
 *  2 用户不存在
 */

if (!$app->logic_permission->check_permission('user_center:reset_user_password')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'uid' => 'required|integer|min:1|return',
    ],
    [
        'uid.*' => '用户ID参数错误',
    ],
    [
        'uid.*' => 1,
    ]);

if(!$user_info = $app->model_user->find_table(['uid'=>$params['uid']], '*', $params['uid'])){
    return_code(2, '用户不存在');
}

//随机生成一个密码
$password = rand_a_password();
$where = [
    'uid' => $params['uid'],
];
$data = [
    'password' => $password,
    'salt' => $user_info['salt'],
];
$app->logic_user->update_user_info($where, $data);

return_json(CODE_SUCCESS, '重置密码成功', ['password' => $password]);