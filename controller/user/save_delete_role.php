<?php
/**
 * @group 角色
 * @name 删除用户的角色
 * @desc
 * @method POST
 * @uri /user/save_delete_role
 * @param integer uid 用户ID 必选
 * @param integer role_id 角色名称 必选
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "删除成功"
 * }
 * @exception
 *  0 删除成功
 *  1 删除失败
 *  2 用户ID有误
 *  3 角色ID有误
 *  4 用户不存在
 *  5 角色不存在
 */

if (!$app->logic_permission->check_permission('user_center:edit_user_role')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'uid' => 'required|integer|min:1|return',
        'role_id' => 'required|integer|min:1|return',
    ],
    [
        'uid.*' => '用户ID有误',
        'role_id.*' => '角色ID有误',
    ],
    [
        'uid.*' => 2,
        'role_id.*' => 3,
    ]);

if (!$check=$app->model_user->find_table(['uid' => $params['uid']], 'uid', $params['uid'])){
    unset($params, $check);
    return_code(4,'用户不存在');
}
if (!$check=$app->model_role->find_table(['id' => $params['role_id']], 'id')){
    unset($params, $check);
    return_code(5,'角色不存在');
}
unset($check);
if (false === $app->model_user_role->delete(['uid'=>$params['uid'], 'role_id'=>$params['role_id']])){
    unset($params);
    return_code(1,'删除失败');
}

unset($params);
//返回结果
return_json(CODE_SUCCESS,'删除成功');
