<?php
/**
 * @group 应用系统
 * @name 添加应用权限页
 * @desc
 * @method GET
 * @uri /application/add_permission/{app_id}
 * @param string app_id 应用ID 必选
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:add_app_permission')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'app_id' => 'required|trim|string|min:3|max:20|return',
    ],
    [
        'app_id.*' => '缺失应用ID',
    ],
    [
        'app_id.*' => 2,
    ]);

if (preg_match('/^[a-zA-Z0-9_]{3,20}$/', $params['app_id'], $matches)==false){
    unset($params);
    return code(3,'应用ID不正确');
}
if (strpos($params['app_id'], 'grant_')===0){
    unset($params);
    return code(4,'应用ID不正确');
}
if (!$application_info=model_application::I()->find_table(['app_id' => $params['app_id']])){
    unset($params, $application_info);
    return code(5,'应用不存在');
}

return result('application/add_permission');