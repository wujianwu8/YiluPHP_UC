<?php
/**
 * @group 应用系统
 * @name 添加应用
 * @desc
 * @method GET
 * @uri /application/add
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:add_application')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

return result('application/add');