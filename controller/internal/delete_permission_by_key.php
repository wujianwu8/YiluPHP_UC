<?php
/**
 * @group 内部接口
 * @name 删除系统一个权限
 * @desc
 * @method GET|POST
 * @uri /internal/delete_permission_by_key
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param string permission_key 权限键 必选 长度在40个字以内
 * @return JSON
 * {
 *  code: 0, //0删除成功，其它为失败
 *  msg: "删除成功",
 *  data: []
 * }
 * @exception
 *   0 删除成功
 */

$params = $app->input->validate(
    [
        'permission_key' => 'required|trim|string|min:2|max:40|return',
        'app_id' => 'required|string|min:1|return',
    ],
    [],
    [
        'permission_key.*' => 1,
    ]);

if (preg_match('/^[a-zA-Z0-9_]{3,25}$/', $params['permission_key'], $matches)==false){
    unset($params,$matches);
    return_code(6,'权限键名只能使用字母、数字、下划线，长度在3-25个字');
}
if (strpos($params['permission_key'], 'grant_')===0){
    unset($params);
    return_code(7,'权限键名不能以grant_开头');
}

//检查权限键名是否存在
if (!$permission_info=$app->model_permission->find_table(['permission_key' => $params['permission_key']], 'permission_id,app_id')){
    unset($params, $permission_info);
    return_code(CODE_SUCCESS,'权限不存在');
}
if ($permission_info['app_id'] != $params['app_id']){
    return_code(CODE_NO_AUTHORIZED, $app->lang('无权操作'));
}


$app->logic_permission->delete_user_permission_cache_by_permission_id($permission_info['permission_id'], $permission_info['app_id']);

if(false === $app->logic_application->delete_permission($permission_info['permission_id'], $permission_info['app_id'], $params['permission_key'])){
    unset($params);
    return_code(1, '删除失败');
}

unset($params);
//返回结果
return_json(CODE_SUCCESS,'删除成功');
