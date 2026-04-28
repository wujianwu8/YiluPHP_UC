<?php
/**
 * @group 邀请链接
 * @name 保存编辑邀请链接
 * @method POST
 * @uri /invitation/save_edit
 */

if (!logic_permission::I()->check_permission('user_center:edit_invitation_link')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'id' => 'required|integer|min:1|return',
        'remark' => 'trim|string|max:255|return',
        'cookie_ttl' => 'integer|return',
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

$data = ['mtime' => time()];
if (isset($params['remark'])) {
    $data['remark'] = $params['remark'];
}
if (isset($params['cookie_ttl'])) {
    $data['cookie_ttl'] = intval($params['cookie_ttl']);
}

model_invitation_link::I()->update_table(['id' => $params['id']], $data);

unset($params, $link_info, $data);
return json(CODE_SUCCESS, YiluPHP::I()->lang('save_successfully'));
