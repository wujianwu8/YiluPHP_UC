<?php
/**
 * @group 用户
 * @name 查看拥有某个角色的用户
 * @desc
 * @method GET
 * @uri /role/users/{role_id}
 * @param integer role_id 权限ID 必选
 * @return html
 * @exception
 *  2 角色ID有误
 *  3 角色不存在
 */

if (!logic_permission::I()->check_permission('user_center:view_user_role')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

$params = input::I()->validate(
    [
        'role_id' => 'required|trim|integer|min:1|return',
    ],
    [
        'role_id.*' => '角色ID有误',
    ],
    [
        'role_id.*' => 2,
    ]);

if (!$check=model_role::I()->find_table(['id' => $params['role_id']], 'id')){
    unset($params, $check);
    return code(3,'角色不存在');
}
unset($check);
//检查操作权限

$where = [
    'role_id' => $params['role_id'],
];
$uids = [];
if($data_list = model_user_role::I()->select_all($where, '', 'uid')) {
    $uids = array_column($data_list, 'uid');
}
if ($uids){
    $user_list = logic_user::I()->select_user_info_by_multi_uids($uids, 'uid,nickname,avatar');
}
else{
    $user_list = [];
}

return result('application/user_nickname_list');