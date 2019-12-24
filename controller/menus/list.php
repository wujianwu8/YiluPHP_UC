<?php
/**
 * @name 全部菜单
 * @desc
 * @method GET
 * @uri /menu/list
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:view_customize_menu')) {
    return_code(100, $app->lang('not_authorized'));
}

$data_list = $app->logic_menus->get_all();
$top_menus = $left_menus = $parent_menus = [];
foreach($data_list as $item){
    if($item['position'] == 'TOP'){
        $top_menus[] = $item;
    }
    if($item['position'] == 'LEFT'){
        $left_menus[] = $item;
    }
    if(empty($item['parent_menu'])){
        $parent_menus[] = $item;
    }
}
$params = [
    'parent_menus' => $parent_menus,
    'top_menus' => $top_menus,
    'left_menus' => $left_menus,
];

unset($data_list, $top_menus, $left_menus, $parent_menus);
return_result('menus/list', $params);