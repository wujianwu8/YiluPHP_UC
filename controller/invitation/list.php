<?php
/**
 * @group 邀请链接
 * @name 邀请链接列表页
 * @method GET
 * @uri /invitation/list
 */

if (!logic_permission::I()->check_permission('user_center:view_invitation_link')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'), 100);
}

$page = input::I()->get_int('page', 1);
$page_size = input::I()->get_int('page_size', 10);
$page_size > 500 && $page_size = 500;
$page_size < 1 && $page_size = 1;

$where = [];
$uid = input::I()->get_trim('uid', null);
if ($uid) {
    $where['uid'] = $uid;
}
$scene = input::I()->get_trim('scene', null);
if ($scene) {
    $where['scene'] = $scene;
}
$invite_code = input::I()->get_trim('invite_code', null);
if ($invite_code) {
    $where['invite_code'] = $invite_code;
}
$remark = input::I()->get_trim('remark', null);
if ($remark) {
    $where['remark'] = $remark;
}

$data_list = model_invitation_link::I()->paging_select_links($where, $page, $page_size);
$data_count = model_invitation_link::I()->count_links($where);

$uids = array_unique(array_column($data_list, 'uid'));
$user_map = [];
if ($uids) {
    $users = logic_user::I()->select_user_info_by_multi_uids($uids, 'uid,nickname,avatar', 'uid');
    $user_map = $users;
}

return result('invitation/list', [
    'data_count' => $data_count,
    'user_map' => $user_map,
]);
