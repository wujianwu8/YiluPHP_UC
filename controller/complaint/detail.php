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

if (!logic_permission::I()->check_permission('user_center:view_complaint_user_list')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'id' => 'required|integer|min:1|return',
    ],
    [
        'id.*' => '参数投诉ID有误',
    ],
    [
        'id.*' => 1,
    ]);

if(!$info = model_user_complaint::I()->find_table(['id' => $params['id']])){
    return code(2, '投诉信息不存在');
}
$uids = [$info['complaint_uid'], $info['respondent_uid']];
$user_info = logic_user::I()->select_user_info_by_multi_uids($uids, 'uid,nickname,avatar');

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
return result('complaint/detail', [
    'complaint_info' => $info,
]);