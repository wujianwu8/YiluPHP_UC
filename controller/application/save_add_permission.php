<?php
/**
 * @group 应用系统
 * @name 保存新增的应用权限
 * @desc
 * @method POST
 * @uri /application/save_add_permission
 * @param string app_id 应用ID 必选
 * @param string permission_name 权限名称 必选
 * @param string permission_key 权限键名 必选 权限键名只能使用字母、数字、下划线，长度在3-25个字，不能以grant_开头，且同一个应用内权限键名不可重复
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
 *  2 应用ID填写有误
 *  3 权限名填写有误
 *  4 权限键名填写有误
 *  5 描述内容太长了
 *  6 权限键名只能使用字母、数字、下划线，长度在3-25个字
 *  7 权限键名不能以grant_开头
 *  8 应用ID有误
 *  9 应用ID有误
 * 10 应用不存在
 * 11 权限键名已经存在，请更换一个吧
 */

if (!logic_permission::I()->check_permission('user_center:add_app_permission')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'app_id' => 'required|trim|string|min:3|max:20|return',
        'permission_name' => 'required|trim|string|min:2|max:40|return',
        'permission_key' => 'required|string|min:3|max:25|return',
        'description' => 'trim|string|max:200|return',
    ],
    [
        'app_id.*' => '应用ID填写有误',
        'permission_name.*' => '权限名填写有误',
        'permission_key.*' => '权限键名填写有误',
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
    return code(6,'权限键名只能使用字母、数字、下划线，长度在3-25个字');
}
if (strpos($params['permission_key'], 'grant_')===0){
    unset($params);
    return code(7,'权限键名不能以grant_开头');
}
if (preg_match('/^[a-zA-Z0-9_]{3,20}$/', $params['app_id'], $matches)==false){
    unset($params,$matches);
    return code(8,'应用ID有误');
}
unset($matches);
if (strpos($params['app_id'], 'grant_')===0){
    unset($params);
    return code(9,'应用ID有误');
}
if (!$check=model_application::I()->find_table(['app_id' => $params['app_id']], 'app_id')){
    unset($params, $check);
    return code(10,'应用不存在');
}
unset($check);
//检查相同的权限键名是否存在
if ($check=model_permission::I()->find_table(['app_id' => $params['app_id'], 'permission_key' => $params['permission_key']], 'app_id')){
    unset($params, $check);
    return code(11,'权限键名已经存在，请更换一个吧');
}
unset($check);

//保存应用入库
$data = [
    'app_id' => $params['app_id'],
    'permission_key' => $params['permission_key'],
    'permission_name' => $params['permission_name'],
    'description' => isset($params['description'])?$params['description']:'',
];
if(false === logic_application::I()->add_permission($data, $self_info['uid'])){
    unset($params, $data);
    return code(1, '保存失败');
}

unset($params, $data);
//返回结果
return json(0,YiluPHP::I()->lang('save_successfully'));
