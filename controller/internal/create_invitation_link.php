<?php
/**
 * @group 内部接口
 * @name 创建邀请链接
 * @method GET|POST
 * @uri /internal/create_invitation_link
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数
 * @param string app_id 应用ID 必选 内部接口公共参数
 * @param integer uid 用户ID 必选
 * @param string scene 场景 必选
 * @param string remark 备注 可选
 * @param integer cookie_ttl cookie有效期 可选 单位秒，默认15天
 * @return JSON
 */

$params = input::I()->validate(
    [
        'uid' => 'required|integer|min:1|return',
        'scene' => 'required|trim|string|min:1|max:32|return',
        'remark' => 'trim|string|max:255|return',
        'cookie_ttl' => 'integer|return',
    ],
    [
        'uid.*' => 'uid参数错误',
        'scene.*' => 'scene参数错误',
    ],
    [
        'uid.*' => 1,
        'scene.*' => 2,
    ]);

if (!logic_invitation::I()->is_valid_scene($params['scene'])) {
    unset($params);
    return code(3, '场景格式不正确');
}

$remark = isset($params['remark']) ? $params['remark'] : '';
$cookie_ttl = isset($params['cookie_ttl']) ? intval($params['cookie_ttl']) : 0;

$link = logic_invitation::I()->create_invitation_link($params['uid'], $params['scene'], $remark, $cookie_ttl);
if (!$link) {
    unset($params, $remark, $cookie_ttl, $link);
    return code(4, YiluPHP::I()->lang('save_failed'));
}

unset($params, $remark, $cookie_ttl);
return json(0, YiluPHP::I()->lang('save_successfully'), [
    'invitation_link' => $link,
]);
