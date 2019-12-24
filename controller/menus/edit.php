<?php
/**
 * @name 添加菜单
 * @desc
 * @method GET
 * @uri /menu/add
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:edit_menu')) {
    return_code(100, $app->lang('not_authorized'));
}

if(!$id = $app->input->get_int('id')){
    throw404();
}
if(!$menu_info = $app->model_menus->find_table(['id'=>$id])){
    throw404();
}

return_result('menus/edit',
    [
        'menu_info' => $menu_info,
        'parent_menus' => $app->model_menus->select_all(
            ['parent_menu'=>0],
            ' position DESC, weight ASC, ctime DESC ',
            'id,lang_key,position'
        )
    ]
);