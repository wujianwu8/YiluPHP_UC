<?php
/**
 * @group 角色
 * @name 保存新增的用户角色
 * @desc
 * @method POST
 * @uri /user/save_add_role
 * @param integer uid 用户ID 必选
 * @param integer role_id 角色名称 必选
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
 *  3 角色ID有误
 *  4 用户不存在
 *  5 角色不存在
 */

if (!logic_permission::I()->check_permission('user_center:edit_user_role')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

$params = input::I()->validate(
    [
        'uid' => 'required|integer|min:1|return',
        'role_id' => 'required|integer|min:1|return',
    ],
    [
        'uid.*' => '用户ID有误',
        'role_id.*' => '角色ID有误',
    ],
    [
        'uid.*' => 2,
        'role_id.*' => 3,
    ]);

if (!$check=model_user::I()->find_table(['uid' => $params['uid']], 'uid', $params['uid'])){
    unset($params, $check);
    return code(4,'用户不存在');
}
if (!$check=model_role::I()->find_table(['id' => $params['role_id']], 'id')){
    unset($params, $check);
    return code(5,'角色不存在');
}
unset($check);
if (false === model_user_role::I()->insert_table(['uid'=>$params['uid'], 'role_id'=>$params['role_id']])){
    unset($params);
    return code(1,'保存失败');
}

unset($params);
//返回结果
return json(CODE_SUCCESS,YiluPHP::I()->lang('save_successfully'));
