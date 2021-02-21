<?php
/**
 * @group 用户
 * @name 用户的详细信息
 * @desc
 * @method GET
 * @uri /user/detail/{uid}
 * @param integer uid 用户 必选 拼接在URI中
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:view_user_detail')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

$params = input::I()->validate(
    [
        'uid' => 'required|integer|min:1|return',
    ],
    [
        'uid.*' => '用户ID参数错误',
    ],
    [
        'uid.*' => 2,
    ]);

if(!$user_info = model_user::I()->find_table(['uid'=>$params['uid']], '*', $params['uid'])){
    return code(3, '用户不存在');
}
$user_identity = model_user_identity::I()->select_all(['uid'=>$params['uid']], '', '*', $params['uid']);
$complaint_count = model_user_complaint::I()->count(['complaint_uid'=>$params['uid']]);
$complaint_count = model_user_complaint::I()->count(['complaint_uid'=>$params['uid']]);

return result('user/detail', [
    'user_info' => $user_info,
    'user_identity' => $user_identity,
    'complaint_count' => model_user_complaint::I()->count(['complaint_uid'=>$params['uid']]),
    'respondent_count' => model_user_complaint::I()->count(['respondent_uid'=>$params['uid']]),
]);