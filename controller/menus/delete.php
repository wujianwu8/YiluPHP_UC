<?php
/**
 * @group 菜单
 * @name 删除菜单
 * @desc
 * @method POST
 * @uri /menus/delete
 * @param string id 菜单ID 必选
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 删除成功
 *  1 删除失败
 *  2 菜单ID有误
 *  3 菜单不存在
 *  4 系统菜单不可以删除
 *  5 此菜单下有子菜单，不可以删除
 */

if (!logic_permission::I()->check_permission('user_center:delete_menu')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

$params = input::I()->validate(
    [
        'id' => 'required|integer|return',
    ],
    [
        'id.*' => '菜单ID有误',
    ],
    [
        'id.*' => 2,
    ]);
//检查操作权限

if(!$menu_info = model_menus::I()->find_table(['id'=>$params['id']])){
    return code(3,'菜单不存在');
}
if ($menu_info['type']=='SYSTEM'){
    unset($menu_info, $params);
    return code(4,'系统菜单不可以删除');
}
unset($menu_info);

if($children_menu = model_menus::I()->find_table(['parent_menu'=>$params['id']])){
    unset($menu_info, $params, $children_menu);
    return code(5,'此菜单下有子菜单，不可以删除');
}
unset($children_menu);

$where = ['id'=>$params['id']];
$res = model_menus::I()->delete($where);
if($res===false){
    unset($params, $where, $res);
    return code(1, '删除失败');
}

//删除所有菜单的缓存
redis_y::I()->del(REDIS_KEY_ALL_MENUS);

unset($params, $where, $res);
//返回结果
return json(CODE_SUCCESS,'删除成功');
