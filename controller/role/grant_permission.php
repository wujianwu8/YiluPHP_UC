<?php
/**
 * @name 给角色授权页
 * @desc
 * @method POST
 * @uri /role/grant_permission/{role_id}
 * @param integer role_id 角色ID 必选
 * @param string app_id 应用ID 可选 默认显示的应用ID
 * @return html
 */

if (!$app->logic_permission->check_permission('user_center:view_role_permission')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'role_id' => 'required|integer|min:1|return',
        'app_id' => 'trim|string|min:1|return',
    ],
    [
        'role_id.*' => '角色ID有误',
        'app_id.*' => '应用ID有误',
    ],
    [
        'role_id.*' => 2,
        'app_id.*' => 3,
    ]);

if(!$role_info = $app->model_role->find_table(['id' => $params['role_id']])){
    unset($params);
    return_code(1, '角色不存在');
}
$current_app_id = null;
if (!empty($params['app_id'])) {
    $current_app_id = $app->model_application->find_table(['app_id' => $params['app_id']], 'app_id');
    $current_app_id = $current_app_id['app_id'];
}

//获取一个已有权限的应用
if($self_app_ids = $app->model_permission->select_role_have_permission_app_id($params['role_id'])){
    $self_app_ids = array_column($self_app_ids, 'app_id');
}

$app_list = $app->model_application->select_all([], '', 'app_id,app_name');
$in_arr = $not_in_arr = [];
foreach ($app_list as $item){
    if (in_array($item['app_id'], $self_app_ids)){
        $in_arr[] = $item;
    }
    else{
        $not_in_arr[] = $item;
    }
}
$app_list = array_merge($in_arr, $not_in_arr);

if (!$current_app_id) {
    $current_app_id = $app->model_application->find_table(['app_id' => $app_list[0]]['app_id'], 'app_id');
    $current_app_id = $current_app_id['app_id'];
}

$having_permission_ids = $app->model_role_permission->select_role_permission($params['role_id'], $current_app_id);
$having_permission_ids = array_column($having_permission_ids, 'permission_id');
$app_permission_list = $app->model_permission->select_all(['app_id'=>$current_app_id],'','permission_id,permission_key,permission_name,description');
foreach ($app_permission_list as $key=>$item){
    $app_permission_list[$key]['permission_name_lang'] = $app->logic_application->translate_permission_name($item['permission_name'],$item['permission_key']);
}
unset($params, $in_arr, $not_in_arr, $self_app_ids);

return_result('role/grant_permission', [
    'page_name' => 'role_permission',
    'app_list' => $app_list,
    'app_permission_list' => $app_permission_list,
    'role_info' => $role_info,
    'current_app_id' => $current_app_id,
    'having_permission_ids' => $having_permission_ids,
]);