<?php
/**
 * @group 邀请链接
 * @name 编辑邀请链接页
 * @method GET
 * @uri /invitation/edit/{id}
 */

if (!logic_permission::I()->check_permission('user_center:edit_invitation_link')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

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
    return code(2, YiluPHP::I()->lang('invitation_link_not_found'));
}

$owner_info = logic_user::I()->find_user_safe_info($link_info['uid']);

return result('invitation/edit');
