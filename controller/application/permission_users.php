<?php
/**
 * @group 应用系统
 * @name 查看拥有某项权限的用户
 * @desc
 * @method GET
 * @uri /application/permission_users/{permission_id}
 * @param string permission_id 权限ID 必选
 * @return html
 * @exception
 *  2 权限ID有误
 *  3 权限不存在
 */

if (!logic_permission::I()->check_permission('user_center:view_app_permission')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'permission_id' => 'required|trim|integer|min:1|return',
    ],
    [
        'permission_id.*' => '权限ID有误',
    ],
    [
        'permission_id.*' => 2,
    ]);

//检查相同的权限键名是否存在
if (!$permission_info=model_permission::I()->find_table(['permission_id' => $params['permission_id']], 'app_id')){
    unset($params, $permission_info);
    return code(3,'权限不存在');
}
//检查操作权限

$where = [
    'permission_id' => $params['permission_id'],
];
$uids = [];
if($data_list = model_user_permission::I()->select_all($where, '', 'uid')) {
    $uids = array_merge($uids,array_column($data_list, 'uid'));
}
if($data_list = model_role_permission::I()->select_all($where, '', 'role_id')) {
    $role_ids = array_column($data_list, 'role_id');
    $where = [
        'role_id' => [
            'symbol' => 'IN',
            'value' => $role_ids,
        ]
    ];
    if($data_list = model_user_role::I()->select_all($where, '', 'uid')) {
        $uids = array_merge($uids,array_column($data_list, 'uid'));
    }
}
if ($uids){
    $user_list = logic_user::I()->select_user_info_by_multi_uids($uids, 'uid,nickname,avatar');
}
else{
    $user_list = [];
}

return result('application/user_nickname_list');