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

if (!$app->logic_permission->check_permission('user_center:view_user_detail')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'uid' => 'required|integer|min:1|return',
    ],
    [
        'uid.*' => '用户ID参数错误',
    ],
    [
        'uid.*' => 2,
    ]);

if(!$user_info = $app->model_user->find_table(['uid'=>$params['uid']], '*', $params['uid'])){
    return_code(3, '用户不存在');
}
$user_identity = $app->model_user_identity->select_all(['uid'=>$params['uid']], '', '*', $params['uid']);
$complaint_count = $app->model_user_complaint->count(['complaint_uid'=>$params['uid']]);
$complaint_count = $app->model_user_complaint->count(['complaint_uid'=>$params['uid']]);

return_result('user/detail', [
    'user_info' => $user_info,
    'user_identity' => $user_identity,
    'complaint_count' => $app->model_user_complaint->count(['complaint_uid'=>$params['uid']]),
    'respondent_count' => $app->model_user_complaint->count(['respondent_uid'=>$params['uid']]),
]);