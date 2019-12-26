<?php
/**
 * @group 用户
 * @name 找回密码页
 * @desc
 * @method GET
 * @uri /find_password
 * @return HTML
 */

if ($self_info){
    $tlt = $app->logic_user->create_login_tlt($self_info['uid'], client_ip());
    $app->logic_user->auto_jump(false, $tlt);
}

return_result('find_password',
    ['area_list' => $app->lib_ip->getAutoAreaList()]
);