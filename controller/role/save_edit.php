<?php
/**
 * @name 保存编辑后的角色
 * @desc
 * @method POST
 * @uri /role/save_edit
 * @param integer role_id 角色ID 必选
 * @param string role_name 角色名 必选 可以是语言键名
 * @param string description 描述 可选
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

if (!$app->logic_permission->check_permission('user_center:edit_role')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'role_id' => 'required|integer|min:1|return',
        'role_name' => 'required|trim|string|min:2|max:40|return',
        'description' => 'trim|string|max:200|return',
    ],
    [
        'role_id.*' => '角色ID参数有误',
        'role_name.*' => '角色名参数有误',
        'description.*' => '描述太长了',
    ],
    [
        'role_id.*' => 2,
        'role_name.*' => 3,
        'description.*' => 4,
    ]);

if (!$check=$app->model_role->find_table(['id' => $params['role_id']], 'id')){
    unset($params, $check);
    return_code(5,'角色不存在');
}
unset($check);

$where = [
    'id' => $params['role_id'],
];
$data = [
    'role_name' => $params['role_name'],
    'description' => $params['description'],
];
if (false===$app->model_role->update_table($where, $data)){
    unset($params, $where, $data);
    return_code(1,'保存失败');
}
unset($params, $where, $data);
//返回结果
return_json(0,'保存成功');
