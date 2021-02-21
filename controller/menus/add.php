<?php
/**
 * @group 菜单
 * @name 添加菜单
 * @desc
 * @method GET
 * @uri /menu/add
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:add_menu')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

return result('menus/add',
    [
        'parent_menus' => model_menus::I()->select_all(
            ['parent_menu'=>0],
            ' position DESC, weight ASC, ctime DESC ',
            'id,lang_key,position'
        )
    ]
);