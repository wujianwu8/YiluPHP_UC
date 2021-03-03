<?php
/**
 * @group 用户
 * @name 保存新增的用户的权限
 * @desc
 * @method POST
 * @uri /user/save_add_permission
 * @param integer uid 用户ID 必选
 * @param integer permission_id 权限名称 必选
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 保存成功
 *  1 保存失败
 *  2 用户ID有误
 *  3 权限ID有误
 *  4 用户不存在
 *  5 权限不存在
 */

if (!logic_permission::I()->check_permission('user_center:edit_user_permission')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

$params = input::I()->validate(
    [
        'uid' => 'required|integer|min:1|return',
        'permission_id' => 'required|integer|min:1|return',
    ],
    [
        'uid.*' => '用户ID有误',
        'permission_id.*' => '权限ID有误',
    ],
    [
        'uid.*' => 2,
        'permission_id.*' => 3,
    ]);

if (!$check=model_user::I()->find_table(['uid' => $params['uid']], 'uid', $params['uid'])){
    unset($params, $check);
    return code(4,'用户不存在');
}
if (!$check=model_permission::I()->find_table(['permission_id' => $params['permission_id']], 'app_id')){
    unset($params, $check);
    return code(5,'权限不存在');
}
if (false === model_user_permission::I()->insert_table(['uid'=>$params['uid'], 'permission_id'=>$params['permission_id']])){
    unset($params, $check);
    return code(1,'保存失败');
}

logic_permission::I()->delete_user_permission_cache_by_permission_id($params['permission_id'], $check['app_id']);
unset($params, $check);
//返回结果
return json(CODE_SUCCESS,YiluPHP::I()->lang('save_successfully'));
