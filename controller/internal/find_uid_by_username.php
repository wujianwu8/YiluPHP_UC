<?php
/**
 * @group 内部接口
 * @name 根据username获取用户ID
 * @desc
 * @method GET|POST
 * @uri /internal/find_uid_by_username
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param string username 用户名 必选 用户名，即可用于登录的用户名
 * @return JSON
 * {
 *  code: 0, //0获取成功
 *  msg: "获取成功",
 *  data: {
 *      uid: 123
 *  }
 * }
 * @exception
 *  0 获取成功
 *  1 username参数错误
 *  2 用户不存在
 */

$params = input::I()->validate(
    [
        'username' => 'required|string|min:1|return',
    ],
    [
        'username.*' => YiluPHP::I()->lang('parameter_error_xxx', ['field'=>'username']),
    ],
    [
        'username.*' => 1,
    ]);
if (!$uid = model_user_identity::I()->find_uid_by_identity('INNER',$params['username'])){
    unset($params, $uid);
    return code(2, YiluPHP::I()->lang('user_not_exist'));
}
unset($params);
return json(0, YiluPHP::I()->lang('successful_get'),['uid'=>$uid]);