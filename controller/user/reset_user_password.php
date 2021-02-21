<?php
/**
 * @group 用户
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

if (!logic_permission::I()->check_permission('user_center:reset_user_password')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

$params = input::I()->validate(
    [
        'uid' => 'required|integer|min:1|return',
    ],
    [
        'uid.*' => '用户ID参数错误',
    ],
    [
        'uid.*' => 1,
    ]);

if(!$user_info = model_user::I()->find_table(['uid'=>$params['uid']], '*', $params['uid'])){
    return code(2, '用户不存在');
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
logic_user::I()->update_user_info($where, $data);

return json(CODE_SUCCESS, '重置密码成功', ['password' => $password]);