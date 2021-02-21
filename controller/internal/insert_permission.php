<?php
/**
 * @group 内部接口
 * @name 给系统添加一个权限
 * @desc
 * @method GET|POST
 * @uri /internal/insert_permission
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param integer uid 用户ID 必选 添加人的用户ID，默认会给此用户赋于权限
 * @param string permission_key 权限键 必选 长度在40个字以内
 * @param string permission_name 权限名称 必选 长度在40个字以内
 * @param string description 权限描述 必选 长度在200个字以内
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
        'permission_name' => 'required|trim|string|min:1|max:40|return',
        'description' => 'trim|string|max:200|return',
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
if(false === logic_application::I()->add_permission($data, $params['uid'])){
    unset($params, $data);
    return code(8, YiluPHP::I()->lang('save_failed'));
}

//删除用户权限缓存
redis_y::I()->del(REDIS_KEY_USER_PERMISSION.$params['uid']);
redis_y::I()->del(REDIS_KEY_USER_PERMISSION.$params['uid'].'_'.$params['app_id']);

unset($params, $data);
//返回结果
return json(0,YiluPHP::I()->lang('save_successfully'));
