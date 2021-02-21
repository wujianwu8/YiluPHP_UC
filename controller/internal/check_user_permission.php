<?php
/**
 * @group 内部接口
 * @name 判断用户是否具有某权限
 * @desc
 * @method GET|POST
 * @uri /internal/check_user_permission
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param integer uid 用户ID 必选
 * @param string permission_key 权限键 必选 不包含app_id
 * @return JSON
 * {
 *  code: 0, //0检查成功
 *  msg: "检查成功",
 *  data: {
 *      result: 1 //检查结果，1为有此权限，0为无此权限
 *  }
 * }
 * @exception
 *   0 检查成功
 *   1 uid参数错误
 *   2 tlt参数错误
 */

$params = input::I()->validate(
    [
        'uid' => 'required|integer|min:1|return',
        'permission_key' => 'required|trim|string|min:1|max:256|return',
        'app_id' => 'required|string|min:1|return',
    ],
    [],
    [
        'uid.*' => 1,
        'permission_key.*' => 2,
    ]);

$res = model_user_permission::I()->if_has_permission($params['uid'], $params['permission_key'], $params['app_id']);
return json(0, YiluPHP::I()->lang('successful_get'), [
    'result' => $res?1:0
]);