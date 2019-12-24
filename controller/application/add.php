<?php
/**
 * @name 添加应用
 * @desc
 * @method GET
 * @uri /application/add
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:add_application')) {
    return_code(100, $app->lang('not_authorized'));
}

return_result('application/add');