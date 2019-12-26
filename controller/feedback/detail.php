<?php
/**
 * @group 用户反馈
 * @name 用户反馈详情页
 * @desc
 * @method GET
 * @uri /feedback/detail
 * @param integer id 投诉ID 必选
 * @return HTML
 * @exception
 *  1 参数反馈ID有误
 *  2 反馈信息不存在
 */

if (!$app->logic_permission->check_permission('user_center:view_feedback')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'id' => 'required|integer|min:1|return',
    ],
    [
        'id.*' => '参数反馈ID有误',
    ],
    [
        'id.*' => 1,
    ]);

if(!$info = $app->model_user_feedback->find_table(['id' => $params['id']])){
    return_code(2, '反馈信息不存在');
}
$uids = [$info['uid']];
$user_info = $app->logic_user->select_user_info_by_multi_uids($uids, 'uid,nickname,avatar');

if (isset($user_info[$info['uid']])) {
    $info['nickname'] = $user_info[$info['uid']]['nickname'];
    $info['avatar'] = $user_info[$info['uid']]['avatar'];
}
else{
    $info['nickname'] = '';
    $info['avatar'] = $config['default_avatar'];
}
unset($user_info, $params);
return_result('feedback/detail', [
    'feedback_info' => $info,
]);