<?php
/**
 * @group 用户
 * @name 添加用户
 * @desc
 * @method GET
 * @uri /user/add
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:add_user')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = [
    'area_list' => lib_ip::getAutoAreaList(),
];

return result('user/add', $params);