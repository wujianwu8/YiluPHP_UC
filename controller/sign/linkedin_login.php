<?php
/**
 * @name Linkedin授权登录的跳转地址
 * @desc
 * @method GET
 * @uri /sign/linkedin_login
 * @param string redirect_uri 跳转页 可选 登录后需要返回到的页面地址
 * @return HTML
 */

$app->oauth_linkedin->login();