<?php
/**
 * @group 角色
 * @name 编辑角色页
 * @desc
 * @method GET
 * @uri /role/edit/{role_id}
 * @param integer role_id 角色ID 必选
 * @return html
 */

if (!logic_permission::I()->check_permission('user_center:edit_role')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'role_id' => 'required|integer|min:1|return',
    ],
    [
        'role_id.*' => '角色ID有误',
    ],
    [
        'role_id.*' => 2,
    ]);

if(!$role_info = model_role::I()->find_table(['id' => $params['role_id']])){
    unset($params);
    return code(1, '角色不存在');
}

return result('role/edit');