<?php
/**
 * @group 内部接口
 * @name 用户退出登录
 * @desc
 * @method GET|POST
 * @uri /internal/sign_out
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param integer uid 用户ID 必选 用户的ID，即uid
 * @return JSON
 * {
 *      code: 0
 *      ,data: true
 *      ,msg: "退出成功"
 * }
 * @exception
 *  0 退出成功
 *  1 uid参数错误
 *  2 退出失败
 */

$params = input::I()->validate(
    [
        'uid' => 'required|integer|min:1|return',
    ],
    [
        'uid.*' => 'uid参数错误',
    ],
    [
        'uid.*' => 1,
    ]);

if($user_info = logic_user::I()->get_login_user_info_by_uid($params['uid'])) {
    logic_user::I()->destroy_login_session($user_info['vk']);
}
unset($user_info, $params);
return json(CODE_SUCCESS, YiluPHP::I()->lang('sign_out_successfully'));