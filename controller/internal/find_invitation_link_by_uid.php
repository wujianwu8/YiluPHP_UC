<?php
/**
 * @group 内部接口
 * @name 根据UID和场景查询邀请链接信息
 * @method GET|POST
 * @uri /internal/find_invitation_link_by_uid
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数
 * @param string app_id 应用ID 必选 内部接口公共参数
 * @param integer uid 用户ID 必选
 * @param string scene 场景 可选 不传则返回该用户所有场景的邀请链接
 * @return JSON
 */

$params = input::I()->validate(
    [
        'uid' => 'required|integer|min:1|return',
        'scene' => 'trim|string|max:32|return',
    ],
    [
        'uid.*' => 'uid参数错误',
    ],
    [
        'uid.*' => 1,
    ]);

$scene = isset($params['scene']) ? $params['scene'] : '';

if ($scene !== '') {
    $link = logic_invitation::I()->find_by_uid_and_scene($params['uid'], $scene);
    unset($params);
    if (!$link) {
        return code(2, '邀请链接不存在');
    }
    return json(0, YiluPHP::I()->lang('successful_get'), [
        'invitation_link' => $link,
    ]);
}

$links = logic_invitation::I()->select_by_uid($params['uid']);
unset($params);
return json(0, YiluPHP::I()->lang('successful_get'), [
    'invitation_links' => $links,
]);
