<?php
/**
 * @group 内部接口
 * @name 根据邀请码查询邀请链接信息
 * @method GET|POST
 * @uri /internal/find_invitation_link_by_code
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数
 * @param string app_id 应用ID 必选 内部接口公共参数
 * @param string invite_code 邀请码 必选
 * @return JSON
 */

$params = input::I()->validate(
    [
        'invite_code' => 'required|trim|string|min:4|max:64|return',
    ],
    [
        'invite_code.*' => '邀请码参数错误',
    ],
    [
        'invite_code.*' => 1,
    ]);

$link = logic_invitation::I()->find_by_invite_code($params['invite_code']);
if (!$link) {
    unset($params, $link);
    return code(2, '邀请链接不存在');
}

$owner_info = logic_user::I()->find_user_safe_info($link['uid']);
unset($params);
return json(0, YiluPHP::I()->lang('successful_get'), [
    'invitation_link' => $link,
    'owner_info' => $owner_info ? $owner_info : null,
]);
