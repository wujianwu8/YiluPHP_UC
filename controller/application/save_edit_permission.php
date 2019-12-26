<?php
/**
 * @group 应用系统
 * @name 保存编辑后的应用权限
 * @desc
 * @method POST
 * @uri /application/save_edit_permission
 * @param string app_id 应用ID 必选
 * @param string permission_key 权限键名 必选 权限键名只能使用字母、数字、下划线，长度在3-25个字，不能以grant_开头，且同一个应用内权限键名不可重复
 * @param string permission_name 权限名称 必选
 * @param string description 描述 可选
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 保存成功
 *  1 保存失败
 *  2 应用ID有误
 *  3 权限名填写有误
 *  4 权限键名有误
 *  5 描述内容太长了
 *  6 权限键名有误
 *  7 权限键名有误
 *  8 应用ID有误
 *  9 应用ID有误
 * 10 应用不存在
 * 11 权限不存在
 */

if (!$app->logic_permission->check_permission('user_center:edit_app_permission')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'app_id' => 'required|trim|string|min:3|max:20|return',
        'permission_name' => 'required|trim|string|min:2|max:40|return',
        'permission_key' => 'required|string|min:3|max:25|return',
        'description' => 'trim|string|max:200|return',
    ],
    [
        'app_id.*' => '应用ID有误',
        'permission_name.*' => '权限名填写有误',
        'permission_key.*' => '权限键名有误',
        'description.*' => '描述内容太长了',
    ],
    [
        'app_id.*' => 2,
        'permission_name.*' => 3,
        'permission_key.*' => 4,
        'description.*' => 5,
    ]);

if (preg_match('/^[a-zA-Z0-9_]{3,25}$/', $params['permission_key'], $matches)==false){
    unset($params,$matches);
    return_code(6,'权限键名有误');
}
if (strpos($params['permission_key'], 'grant_')===0){
    unset($params);
    return_code(7,'权限键名有误');
}
if (preg_match('/^[a-zA-Z0-9_]{3,20}$/', $params['app_id'], $matches)==false){
    unset($params,$matches);
    return_code(8,'应用ID有误');
}
unset($matches);
if (strpos($params['app_id'], 'grant_')===0){
    unset($params);
    return_code(9,'应用ID有误');
}
//检查相同的权限键名是否存在
if (!$check=$app->model_permission->find_table(['app_id' => $params['app_id'], 'permission_key' => $params['permission_key']], 'app_id')){
    unset($params, $check);
    return_code(11,'权限不存在');
}
unset($check);
if (!$check=$app->model_application->find_table(['app_id' => $params['app_id']], 'app_id')){
    unset($params, $check);
    return_code(10,'应用不存在');
}
unset($check);

//保存应用入库
$data = [
    'permission_name' => $params['permission_name'],
    'description' => isset($params['description'])?$params['description']:'',
];
if(false === $app->logic_application->update_permission($params['app_id'], $params['permission_key'], $data)){
    unset($params, $data);
    return_code(1, '保存失败');
}

unset($params, $data);
//返回结果
return_json(0,'保存成功');
