<?php
/**
 * @group 菜单
 * @name 添加菜单
 * @desc
 * @method GET
 * @uri /menu/add
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:edit_menu')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

if(!$id = input::I()->get_int('id')){
    throw404();
}
if(!$menu_info = model_menus::I()->find_table(['id'=>$id])){
    throw404();
}

return result('menus/edit',
    [
        'parent_menus' => model_menus::I()->select_all(
            ['parent_menu'=>0],
            ' position DESC, weight ASC, ctime DESC ',
            'id,lang_key,position'
        )
    ]
);