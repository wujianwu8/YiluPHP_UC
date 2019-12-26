<?php
/**
 * @group 角色
 * @name 创建角色页
 * @desc
 * @method GET
 * @uri /role/add
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:add_role')) {
    return_code(100, $app->lang('not_authorized'));
}

return_result('role/add');