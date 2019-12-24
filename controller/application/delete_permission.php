<?php
/**
 * @name 删除应用权限
 * @desc
 * @method POST
 * @uri /application/delete_permission
 * @param string permission_id 权限ID 必选
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "删除成功"
 * }
 * @exception
 *  0 删除成功
 *  1 删除失败
 *  2 权限ID有误
 *  3 权限不存在
 */

if (!$app->logic_permission->check_permission('user_center:delete_app_permission')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'permission_id' => 'required|trim|integer|min:1|return',
    ],
    [
        'permission_id.*' => '权限ID有误',
    ],
    [
        'permission_id.*' => 2,
    ]);

//检查相同的权限键名是否存在
if (!$permission_info=$app->model_permission->find_table(['permission_id' => $params['permission_id']], 'app_id,permission_key')){
    unset($params, $permission_info);
    return_code(3,'权限不存在');
}
//检查操作权限

$app->logic_permission->delete_user_permission_cache_by_permission_id($params['permission_id'], $permission_info['app_id']);

if(false === $app->logic_application->delete_permission($params['permission_id'], $permission_info['app_id'], $permission_info['permission_key'])){
    unset($params);
    return_code(1, '删除失败');
}

unset($params);
//返回结果
return_json(CODE_SUCCESS,'删除成功');
