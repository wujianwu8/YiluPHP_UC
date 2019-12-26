<?php
/**
 * @group 用户投诉
 * @name 用户投诉详情页
 * @desc
 * @method GET
 * @uri /complaint/detail
 * @param integer id 投诉ID 必选
 * @return HTML
 * @exception
 *  1 参数投诉ID有误
 *  2 投诉不存在
 */

if (!$app->logic_permission->check_permission('user_center:view_complaint_user_list')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'id' => 'required|integer|min:1|return',
    ],
    [
        'id.*' => '参数投诉ID有误',
    ],
    [
        'id.*' => 1,
    ]);

if(!$info = $app->model_user_complaint->find_table(['id' => $params['id']])){
    return_code(2, '投诉信息不存在');
}
$uids = [$info['complaint_uid'], $info['respondent_uid']];
$user_info = $app->logic_user->select_user_info_by_multi_uids($uids, 'uid,nickname,avatar');

if (isset($user_info[$info['respondent_uid']])) {
    $info['respondent_nickname'] = $user_info[$info['respondent_uid']]['nickname'];
    $info['respondent_avatar'] = $user_info[$info['respondent_uid']]['avatar'];
}
else{
    $info['respondent_nickname'] = '';
    $info['respondent_avatar'] = $config['default_avatar'];
}
if (isset($user_info[$info['complaint_uid']])) {
    $info['complaint_nickname'] = $user_info[$info['complaint_uid']]['nickname'];
    $info['complaint_avatar'] = $user_info[$info['complaint_uid']]['avatar'];
}
else{
    $info['complaint_nickname'] = '';
    $info['complaint_avatar'] = $config['default_avatar'];
}
unset($user_info, $params);
return_result('complaint/detail', [
    'complaint_info' => $info,
]);