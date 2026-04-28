<?php
/**
 * @group 邀请链接
 * @name 保存新邀请链接
 * @method POST
 * @uri /invitation/save_add
 */

if (!logic_permission::I()->check_permission('user_center:add_invitation_link')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

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
    return code(3, '场景格式不正确，只能使用小写字母、数字和下划线');
}

if (model_invitation_link::I()->find_by_uid_and_scene($params['uid'], $params['scene'])) {
    unset($params);
    return code(4, '该用户在此场景下已有邀请链接');
}

$remark = isset($params['remark']) ? $params['remark'] : '';
$cookie_ttl = isset($params['cookie_ttl']) ? intval($params['cookie_ttl']) : 0;

$link = logic_invitation::I()->create_invitation_link($params['uid'], $params['scene'], $remark, $cookie_ttl);
if (!$link) {
    unset($params, $remark, $cookie_ttl, $link);
    return code(5, YiluPHP::I()->lang('save_failed'));
}

unset($params, $remark, $cookie_ttl, $link);
return json(CODE_SUCCESS, YiluPHP::I()->lang('save_successfully'));
