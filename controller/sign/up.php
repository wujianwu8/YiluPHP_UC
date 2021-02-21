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
    $tlt = logic_user::I()->create_login_tlt($self_info['uid'], client_ip());
    logic_user::I()->auto_jump(false, $tlt);
}

return result('sign/up', [
    'area_list' => lib_ip::I()->getAutoAreaList()
]);