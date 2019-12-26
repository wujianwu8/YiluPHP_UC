<?php
/**
 * @group 用户
 * @name 注册页（UI界面）
 * @desc
 * @method GET
 * @uri /sign_up
 * @param string redirect_uri 跳转页 登录后需要返回到的页面url
 * @return HTML
 */


if ($self_info){
    $tlt = $app->logic_user->create_login_tlt($self_info['uid'], client_ip());
    $app->logic_user->auto_jump(false, $tlt);
}

return_result('sign/up', [
    'area_list' => $app->lib_ip->getAutoAreaList()
]);