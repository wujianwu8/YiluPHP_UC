<?php
/**
 * @name 登录页（UI界面）
 * @desc
 * @method GET
 * @uri /sign_in
 * @param string redirect_uri 跳转页 可选 登录后需要返回到的页面url
 * @return HTML
 */


$params = [
    'area_list' => $app->lib_ip->getAutoAreaList(),
];
return_result('sign_in', $params);