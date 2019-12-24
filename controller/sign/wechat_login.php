<?php
/**
 * @name 微信公众平台（服务号或开放平台）授权登录的跳转地址
 * @desc
 * @method GET
 * @uri /sign/wechat_login
 * @param integer open 是否开放平台 可选 0为商户服务号，1为开放平台，默认为商户服务号
 * @param string redirect_uri 跳转页 可选 登录后需要返回到的页面地址
 * @return HTML
 */

$open = $app->input->get_int('open', 0);
if ($open){
    $scope = 'snsapi_login';
}
else{
    $scope = 'snsapi_userinfo';
}
$callback = null;
if ($app->input->get_trim('for_bind', null)){
    if ($open){
        $callback = $config['oauth_plat']['wechat_open']['callback'].'/for_bind/1';
    }
    else{
        $callback = $config['oauth_plat']['wechat']['callback'].'/for_bind/1';
    }
}
$app->oauth_wechat->login($scope, null, $callback);