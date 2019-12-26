<?php
/**
 * @group 用户
 * @name 删除的用户的权限
 * @desc
 * @method POST
 * @uri /user/save_delete_permission
 * @param integer uid 用户ID 必选
 * @param integer permission_id 权限名称 必选
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 保存成功
 *  1 保存失败
 *  2 用户ID有误
 *  3 权限ID有误
 *  4 用户不存在
 *  5 权限不存在
 */

if (!$app->logic_permission->check_permission('user_center:edit_user_permission')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'uid' => 'required|integer|min:1|return',
        'permission_id' => 'required|integer|min:1|return',
    ],
    [
        'uid.*' => '用户ID有误',
        'permission_id.*' => '权限ID有误',
    ],
    [
        'uid.*' => 2,
        'permission_id.*' => 3,
    ]);

if (!$check=$app->model_user->find_table(['uid' => $params['uid']], 'uid', $params['uid'])){
    unset($params, $check);
    return_code(4,'用户不存在');
}
if (!$check=$app->model_permission->find_table(['permission_id' => $params['permission_id']], 'app_id')){
    unset($params, $check);
    return_code(5,'权限不存在');
}

$app->logic_permission->delete_user_permission_cache_by_permission_id($params['permission_id'], $check['app_id']);
if (false === $app->model_user_permission->delete(['uid'=>$params['uid'], 'permission_id'=>$params['permission_id']])){
    unset($params, $check);
    return_code(1,'保存失败');
}
unset($params, $check);
//返回结果
return_json(CODE_SUCCESS,'保存成功');
