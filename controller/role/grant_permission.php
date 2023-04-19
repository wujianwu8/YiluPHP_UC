<?php
/**
 * @group 角色
 * @name 给角色授权页
 * @desc
 * @method POST
 * @uri /role/grant_permission/{role_id}
 * @param integer role_id 角色ID 必选
 * @param string app_id 应用ID 可选 默认显示的应用ID
 * @return html
 */

if (!logic_permission::I()->check_permission('user_center:view_role_permission')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
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

if(!$role_info = model_role::I()->find_table(['id' => $params['role_id']])){
    unset($params);
    return code(1, '角色不存在');
}
$current_app_id = null;
if (!empty($params['app_id'])) {
    $current_app_id = model_application::I()->find_table(['app_id' => $params['app_id']], 'app_id');
    $current_app_id = $current_app_id['app_id'];
}

//获取一个已有权限的应用
if($self_app_ids = model_permission::I()->select_role_have_permission_app_id($params['role_id'])){
    $self_app_ids = array_column($self_app_ids, 'app_id');
}

$app_list = model_application::I()->select_all([], '', 'app_id,app_name');
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
    $current_app_id = model_application::I()->find_table(['app_id' => $app_list[0]]['app_id'], 'app_id');
    $current_app_id = $current_app_id['app_id'];
}

//角色拥有的权限
$having_permission_ids = model_role_permission::I()->select_role_permission($params['role_id'], $current_app_id);
$having_permission_ids = array_column($having_permission_ids, 'permission_id');
//当前登录用户拥有的可分配权限
if ($self_info['uid']==1) { //超级管理员读取所有的权限
    $app_permission_list = model_permission::I()->select_all(['app_id' => $current_app_id], '', 'permission_id,permission_key,permission_name,description');
}
else{
    $app_permission_list = logic_permission::I()->select_user_permission_can_grant_in_app($self_info['uid'], $current_app_id);
}
foreach ($app_permission_list as $key=>$item){
    $app_permission_list[$key]['permission_name_lang'] = logic_application::I()->translate_permission_name($item['permission_name'],$item['permission_key']);
}
unset($params, $in_arr, $not_in_arr, $self_app_ids);

return result('role/grant_permission', [
    'page_name' => 'role_permission',
]);