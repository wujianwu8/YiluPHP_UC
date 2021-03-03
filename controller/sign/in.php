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

if (!empty($self_info['uid'])){
    if(logic_user::I()->get_login_user_info_by_uid($self_info['uid'])) {
        $tlt = logic_user::I()->create_login_tlt($self_info['uid'], client_ip());
        logic_user::I()->auto_jump(false, $tlt);
    }
}

$params = [
    'area_list' => lib_ip::getAutoAreaList(),
];

return result('sign/in', $params);