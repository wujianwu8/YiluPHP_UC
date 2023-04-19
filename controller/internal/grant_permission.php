<?php
/**
 * @group 内部接口
 * @name 给用户授予一个权限
 * @desc 注册在调用接口时判断授权人是否有权向他人授予此权限
 * @method GET|POST
 * @uri /internal/grant_permission
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param integer uid 用户ID 必选 被授权人的用户ID
 * @param string permission_key 权限键 必选 长度在40个字以内，不含用户ID
 * @return JSON
 * {
 *  code: 0, //0保存成功，其它为失败
 *  msg: "保存成功",
 *  data: []
 * }
 * @exception
 *   0 保存成功
 *   8 保存失败
 */

$params = input::I()->validate(
    [
        'uid' => 'required|integer|return',
        'permission_key' => 'required|trim|string|min:2|max:40|return',
        'app_id' => 'required|string|min:1|return',
    ],
    [],
    [
        'uid.*' => 1,
        'permission_name.*' => 2,
        'description.*' => 3,
    ]);

if (preg_match('/^[a-zA-Z0-9_]{3,25}$/', $params['permission_key'], $matches)==false){
    unset($params,$matches);
    return code(6,'权限键名只能使用字母、数字、下划线，长度在3-25个字');
}
if (strpos($params['permission_key'], 'grant_')===0){
    unset($params);
    return code(7,'权限键名不能以grant_开头');
}
//检查权限是否存在
if (!$permission_info=model_permission::I()->find_table(['app_id' => $params['app_id'], 'permission_key' => $params['permission_key']], 'app_id,permission_id')){
    unset($params, $check);
    return code(11,'权限不存在');
}

//检查用户是否存在
if (!$check=model_user::I()->find_table(['uid' => $params['uid']], 'uid', $params['uid'])){
    unset($params, $check);
    return code(12,'用户不存在');
}

//检查此用户是否已经拥有此权限
if ($check=model_user_permission::I()->find_table(['uid'=>$params['uid'], 'permission_id'=>$permission_info['permission_id']])){
    unset($params, $check);
    //返回结果
    return json(0,YiluPHP::I()->lang('save_successfully'));
}
unset($check);

if (false === model_user_permission::I()->insert_table(['uid'=>$params['uid'], 'permission_id'=>$permission_info['permission_id']])){
    unset($params);
    return code(1,'保存失败');
}

//删除用户权限缓存
logic_permission::I()->delete_user_permission_cache_by_permission_id($permission_info['permission_id'], $params['app_id']);

unset($params, $data);
//返回结果
return json(0,YiluPHP::I()->lang('save_successfully'));
