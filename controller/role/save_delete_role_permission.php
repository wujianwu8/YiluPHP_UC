<?php
/**
 * @group 角色
 * @name 保存删除的角色的权限
 * @desc
 * @method POST
 * @uri /application/save_delete_role_permission
 * @param integer role_id 角色ID 必选
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
 *  2 角色ID有误
 *  3 权限ID有误
 *  4 角色不存在
 *  5 权限不存在
 */

if (!logic_permission::I()->check_permission('user_center:edit_role_permission')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'role_id' => 'required|integer|min:1|return',
        'permission_id' => 'required|integer|min:1|return',
    ],
    [
        'role_id.*' => '角色ID有误',
        'permission_id.*' => '权限ID有误',
    ],
    [
        'role_id.*' => 2,
        'permission_id.*' => 3,
    ]);

if (!$check=model_role::I()->find_table(['id' => $params['role_id']], 'id')){
    unset($params, $check);
    return code(4,'角色不存在');
}
if (!$check=model_permission::I()->find_table(['permission_id' => $params['permission_id']], 'app_id')){
    unset($params, $check);
    return code(5,'权限不存在');
}

logic_permission::I()->delete_user_permission_cache_by_role_id($params['role_id'], $check['app_id']);
if (false === model_role_permission::I()->delete(['role_id'=>$params['role_id'], 'permission_id'=>$params['permission_id']])){
    unset($params, $check);
    return code(1,'保存失败');
}
unset($params, $check);
//返回结果
return json(0,'保存成功');
