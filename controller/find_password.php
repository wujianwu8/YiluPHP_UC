<?php
/**
 * @group 用户
 * @name 找回密码页
 * @desc
 * @method GET
 * @uri /find_password
 * @return HTML
 */

if (!empty($self_info['uid'])){
    $tlt = logic_user::I()->create_login_tlt($self_info['uid'], client_ip());
    logic_user::I()->auto_jump(false, $tlt);
}

return result('find_password',
    ['area_list' => lib_ip::I()->getAutoAreaList()]
);