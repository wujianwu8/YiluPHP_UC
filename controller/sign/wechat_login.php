<?php
/**
 * @group 用户
 * @name 微信公众平台（服务号或开放平台）授权登录的跳转地址
 * @desc
 * @method GET
 * @uri /sign/wechat_login
 * @param integer open 是否开放平台 可选 0为商户服务号，1为开放平台，默认为商户服务号
 * @param string redirect_uri 跳转页 可选 登录后需要返回到的页面地址
 * @return HTML
 */

$open = input::I()->get_int('open', 0);
$for_base = input::I()->get_int('for_base', 0);
if ($open==1){
    $scope = 'snsapi_login';
}
else{
    if ($for_base==1){ //仅获取 openid和UnionID
        $scope = 'snsapi_base';
    }
    else{
        $scope = 'snsapi_userinfo';
    }
}
$callback = null;
if (input::I()->get_trim('for_bind', null)){
    if ($open==1){
        $callback = $config['oauth_plat']['wechat_open']['callback'].'/for_bind/1';
    }
    else{
        $callback = $config['oauth_plat']['wechat']['callback'].'/for_bind/1';
    }
}
elseif ($for_base==1){ //仅获取 openid和UnionID
    $callback = $config['oauth_plat']['wechat']['callback'].'/for_base/1';
    if ($callback2 = input::I()->get_trim('callback','/')) {
        $_SESSION['callback_for_wx_base'] = urldecode($callback2);
    }
}
oauth_wechat::I()->login($scope, null, $callback);