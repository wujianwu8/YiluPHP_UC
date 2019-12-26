<?php
/**
 * @group 用户
 * @name 登录页（UI界面）
 * @desc
 * @method GET
 * @uri /sign_in
 * @param string redirect_uri 跳转页 可选 登录后需要返回到的页面url
 * @return HTML
 */

if ($self_info){
    if($app->logic_user->get_login_user_info_by_uid($self_info['uid'])) {
        $tlt = $app->logic_user->create_login_tlt($self_info['uid'], client_ip());
        $app->logic_user->auto_jump(false, $tlt);
    }
}

$params = [
    'area_list' => $app->lib_ip->getAutoAreaList(),
];

return_result('sign/in', $params);