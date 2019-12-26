<?php
/**
 * @group 菜单
 * @name 添加菜单
 * @desc
 * @method GET
 * @uri /menu/add
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:add_menu')) {
    return_code(100, $app->lang('not_authorized'));
}

return_result('menus/add',
    [
        'parent_menus' => $app->model_menus->select_all(
            ['parent_menu'=>0],
            ' position DESC, weight ASC, ctime DESC ',
            'id,lang_key,position'
        )
    ]
);