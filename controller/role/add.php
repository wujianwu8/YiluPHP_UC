<?php
/**
 * @group 角色
 * @name 创建角色页
 * @desc
 * @method GET
 * @uri /role/add
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:add_role')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

return result('role/add');