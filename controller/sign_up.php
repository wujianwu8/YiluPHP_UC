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

return_result('sign_up', [
    'area_list' => $GLOBALS['app']->lib_ip->getAutoAreaList()
]);