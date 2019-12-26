<?php
/**
 * @group 用户
 * @name QQ授权登录的跳转地址
 * @desc
 * @method GET
 * @uri /sign/qq_login
 * @param string redirect_uri 跳转页 可选 登录后需要返回到的页面地址
 * @return HTML
 */

$callback = null;
if ($app->input->get_trim('for_bind', null)){
    $callback = $config['oauth_plat']['qq']['callback'].'/for_bind/1';
}
$app->oauth_qq->login($callback);