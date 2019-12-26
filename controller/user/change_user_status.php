<?php
/**
 * @group 用户
 * @name 修改用户状态
 * @desc
 * @method POST
 * @uri /user/change_user_status
 * @param integer uid 用户ID 必选
 * @param integer status 用户状态 必选 0禁用,1启用
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "操作成功"
 * }
 * @exception
 *  0 操作成功
 *  1 用户ID参数错误
 *  2 用户状态参数错误
 *  3 用户不存在
 */

if (!$app->logic_permission->check_permission('user_center:edit_user_status')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'uid' => 'required|integer|min:1|return',
        'status' => 'required|integer|min:0|max:1|return',
    ],
    [
        'uid.*' => '用户ID参数错误',
        'status.*' => '用户状态参数错误',
    ],
    [
        'uid.*' => 1,
        'status.*' => 2,
    ]);

if(!$user_info = $app->model_user->find_table(['uid'=>$params['uid']], '*', $params['uid'])){
    return_code(2, '用户不存在');
}

$where = [
    'uid' => $params['uid'],
];
$data = [
    'status' => $params['status'],
];
$app->logic_user->update_user_info($where, $data);

return_json(CODE_SUCCESS, '操作成功');