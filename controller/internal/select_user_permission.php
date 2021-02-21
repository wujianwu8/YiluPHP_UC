<?php
/**
 * @group 内部接口
 * @name 获取用户的所有权限
 * @desc
 * @method GET|POST
 * @uri /internal/select_user_permission
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param integer uid 用户ID 必选
 * @return JSON
 * {
 *  code: 0, //0检查成功
 *  msg: "检查成功",
 *  data: {
 *      permission_list: [] //所有权限数组
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
        'app_id' => 'required|string|min:1|return',
    ],
    [],
    [
        'uid.*' => 1,
        'app_id.*' => 2,
    ]);

$res = model_user_permission::I()->select_permissions_user_already_has($params['uid'], $params['app_id']);
return json(0, YiluPHP::I()->lang('successful_get'), [
    'permission_list' => $res
]);