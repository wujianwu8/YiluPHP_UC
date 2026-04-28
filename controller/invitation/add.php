<?php
/**
 * @group 邀请链接
 * @name 添加邀请链接页
 * @method GET
 * @uri /invitation/add
 */

if (!logic_permission::I()->check_permission('user_center:add_invitation_link')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

return result('invitation/add');
