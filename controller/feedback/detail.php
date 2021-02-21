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

if (!logic_permission::I()->check_permission('user_center:view_feedback')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

$params = input::I()->validate(
    [
        'id' => 'required|integer|min:1|return',
    ],
    [
        'id.*' => '参数反馈ID有误',
    ],
    [
        'id.*' => 1,
    ]);

if(!$info = model_user_feedback::I()->find_table(['id' => $params['id']])){
    throw new validate_exception('反馈信息不存在',2);
}
$uids = [$info['uid']];
$user_info = logic_user::I()->select_user_info_by_multi_uids($uids, 'uid,nickname,avatar');

if (isset($user_info[$info['uid']])) {
    $info['nickname'] = $user_info[$info['uid']]['nickname'];
    $info['avatar'] = $user_info[$info['uid']]['avatar'];
}
else{
    $info['nickname'] = '';
    $info['avatar'] = $config['default_avatar'];
}
unset($user_info, $params);
return result('feedback/detail', [
    'feedback_info' => $info,
]);