<?php
/**
 * @group 邀请注册
 * @name 更换邀请码
 * @method POST
 * @uri /invitation/change_code
 */

$params = input::I()->validate(
    [
        'id' => 'required|integer|min:1|return',
    ],
    [
        'id.*' => 'ID参数错误',
    ],
    [
        'id.*' => 1,
    ]);

if (!$link_info = model_invitation_link::I()->find_table(['id' => $params['id']])) {
    unset($params, $link_info);
    return code(2, YiluPHP::I()->lang('invitation_link_not_found'));
}

if ($link_info['uid'] != $GLOBALS['self_info']['uid']) {
    unset($params, $link_info);
    return code(3, YiluPHP::I()->lang('not_authorized'));
}

$link = logic_invitation::I()->refresh_invite_code($params['id']);
if (!$link) {
    unset($params, $link_info, $link);
    return code(4, YiluPHP::I()->lang('save_failed'));
}

unset($params, $link_info);
return json(CODE_SUCCESS, YiluPHP::I()->lang('save_successfully'), [
    'invite_code' => $link['invite_code'],
    'invite_url' => logic_invitation::I()->build_register_url($link['invite_code']),
]);
