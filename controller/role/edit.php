<?php
/**
 * @name 编辑角色页
 * @desc
 * @method GET
 * @uri /role/edit/{role_id}
 * @param integer role_id 角色ID 必选
 * @return html
 */

if (!$app->logic_permission->check_permission('user_center:edit_role')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'role_id' => 'required|integer|min:1|return',
    ],
    [
        'role_id.*' => '角色ID有误',
    ],
    [
        'role_id.*' => 2,
    ]);

if(!$role_info = $app->model_role->find_table(['id' => $params['role_id']])){
    unset($params);
    return_code(1, '角色不存在');
}

return_result('role/edit', [
    'role_info' => $role_info,
]);