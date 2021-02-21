<?php
/**
 * @group 角色
 * @name 删除角色
 * @desc
 * @method POST
 * @uri /role/save_edit
 * @param integer role_id 角色ID 必选
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 保存成功
 *  1 保存失败
 *  2 角色ID参数有误
 *  3 角色名参数有误
 *  4 描述太长了
 *  5 角色不存在
 */

if (!logic_permission::I()->check_permission('user_center:delete_role')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'role_id' => 'required|integer|min:1|return',
    ],
    [
        'role_id.*' => '角色ID参数有误',
    ],
    [
        'role_id.*' => 2,
    ]);

if (!$check=model_role::I()->find_table(['id' => $params['role_id']], 'id')){
    unset($params, $check);
    return code(3,'角色不存在');
}
unset($check);

if (false===logic_role::I()->delete_role($params['role_id'])){
    unset($params);
    return code(1,'删除失败');
}
logic_permission::I()->delete_user_permission_cache_by_role_id($params['role_id']);
unset($params);
//返回结果
return json(CODE_SUCCESS,'删除成功');
