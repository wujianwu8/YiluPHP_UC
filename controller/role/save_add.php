<?php
/**
 * @group 角色
 * @name 保存新角色
 * @desc
 * @method POST
 * @uri /role/save_add
 * @param string role_name 角色名 必选 可以是语言键名
 * @param string description 描述 可选
 * @return json
 * {
 *      code: 0
 *      ,data: [
 *          role_id:666
 *      ]
 *      ,msg: "创建成功"
 * }
 * @exception
 *  0 创建成功
 *  1 创建失败
 *  2 角色名参数有误
 *  3 描述太长了
 */

if (!logic_permission::I()->check_permission('user_center:add_role')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'role_name' => 'required|trim|string|min:2|max:40|return',
        'description' => 'trim|string|max:200|return',
    ],
    [
        'role_name.*' => '角色名参数有误',
        'description.*' => '描述太长了',
    ],
    [
        'role_name.*' => 2,
        'description.*' => 3,
    ]);

//保存入库
if(false === $role_id=model_role::I()->insert_table($params)){
    unset($params);
    return code(1, '创建失败');
}

unset($params);
//返回结果
return json(CODE_SUCCESS,'创建成功', ['role_id'=>$role_id]);
