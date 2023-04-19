<?php
/**
 * @group 应用系统
 * @name 编辑应用权限页
 * @desc
 * @method GET
 * @uri /application/edit_permission/{permission_id}
 * @param string permission_id 权限ID 必选
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:edit_app_permission')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'permission_id' => 'required|trim|integer|min:1|return',
    ],
    [
        'permission_id.*' => '缺失权限ID参数',
    ],
    [
        'permission_id.*' => 2,
    ]);

if (!$permission_info=model_permission::I()->find_table(['permission_id' => $params['permission_id']])){
    unset($params, $permission_info);
    return code(3,'权限不存在');
}
if ($permission_info['is_fixed']){
    unset($params, $permission_info);
    return code(4,'系统应用的权限不可编辑');
}

if (!$application_info=model_application::I()->find_table(['app_id' => $permission_info['app_id']])){
    unset($params, $application_info, $permission_info);
    return code(5,'应用ID不存在：'.$permission_info['app_id']);
}
$permission_info['permission_name_lang'] = logic_application::I()->translate_permission_name($permission_info['permission_name'],$permission_info['permission_key']);
return result('application/edit_permission');