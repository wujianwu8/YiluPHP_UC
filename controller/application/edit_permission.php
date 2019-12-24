<?php
/**
 * @name 编辑应用权限页
 * @desc
 * @method GET
 * @uri /application/edit_permission/{permission_id}
 * @param string permission_id 权限ID 必选
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:edit_app_permission')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'permission_id' => 'required|trim|integer|min:1|return',
    ],
    [
        'permission_id.*' => '缺失权限ID参数',
    ],
    [
        'permission_id.*' => 2,
    ]);

if (!$permission_info=$app->model_permission->find_table(['permission_id' => $params['permission_id']])){
    unset($params, $permission_info);
    return_code(3,'权限不存在');
}
if ($permission_info['is_fixed']){
    unset($params, $permission_info);
    return_code(4,'系统应用的权限不可编辑');
}

if (!$application_info=$app->model_application->find_table(['app_id' => $permission_info['app_id']])){
    unset($params, $application_info, $permission_info);
    return_code(5,'应用ID不存在：'.$permission_info['app_id']);
}
$permission_info['permission_name_lang'] = $app->logic_application->translate_permission_name($permission_info['permission_name'],$permission_info['permission_key']);
return_result('application/edit_permission', [
    'application_info' => $application_info,
    'permission_info' => $permission_info,
]);