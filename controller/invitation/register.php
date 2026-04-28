<?php
/**
 * @group 邀请注册
 * @name 邀请注册页
 * @method GET
 * @uri /invitation/register
 */

$uid = $GLOBALS['self_info']['uid'];

$my_link = logic_invitation::I()->find_by_uid_and_scene($uid, logic_invitation::SCENE_REGISTER);
if (!$my_link) {
    $my_link = logic_invitation::I()->create_invitation_link($uid, logic_invitation::SCENE_REGISTER, '邀请注册');
}
$invite_url = logic_invitation::I()->build_register_url($my_link['invite_code']);

$my_user_info = model_user::I()->find_table(['uid' => $uid], '*', $uid);
$inviter_info = null;
if (!empty($my_user_info['register_inviter_uid'])) {
    $inviter_info = logic_user::I()->find_user_safe_info($my_user_info['register_inviter_uid']);
}

$page = input::I()->get_int('page', 1);
$page_size = 10;
$invited_count = logic_user::I()->count_register_invited_users($uid);
$invited_users = logic_user::I()->paging_select_register_invited_users($uid, $page, $page_size);

global $config;
foreach ($invited_users as $key => $user) {
    if (empty($user['avatar'])) {
        $invited_users[$key]['avatar'] = $config['default_avatar'];
    }
}

return result('invitation/register', [
    'invite_url' => $invite_url,
    'inviter_info' => $inviter_info,
    'invited_count' => $invited_count,
]);
