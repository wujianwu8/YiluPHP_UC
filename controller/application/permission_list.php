<?php
/**
 * @name 应用权限管理页
 * @desc
 * @method GET
 * @uri /application/permission_list/{app_id}
 * @param string app_id 应用ID 可选 默认为全部
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:view_app_permission')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
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
    return_code(3,'应用ID不正确');
}
if (strpos($params['app_id'], 'grant_')===0){
    unset($params);
    return_code(4,'应用ID不正确');
}
if (!$application_info=$app->model_application->find_table(['app_id' => $params['app_id']])){
    unset($params, $application_info);
    return_code(5,'应用不存在');
}

$where = [
    'app_id' => $params['app_id'],
//    'permission_key' => [
//        'symbol' => 'NOT LIKE',
//        'value' => 'grant_%',
//    ],
];
$data_list = $app->model_permission->select_all($where, 'permission_id DESC');
foreach ($data_list as $key => $item){
    $data_list[$key]['permission_name_lang'] = $app->logic_application->translate_permission_name($item['permission_name'],$item['permission_key']);
}
return_result('application/permission_list', [
    'application_info' => $application_info,
    'data_list' => $data_list,
]);