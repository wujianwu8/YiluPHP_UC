<?php
/**
 * @name 登录后的主面板页
 * @desc
 * @method GET
 * @uri /sign_in
 * @param string redirect_uri 跳转页 可选 登录后需要返回到的页面url
 * @return HTML
 */

//echo '<meta charset="utf-8">';

//for ($i=0; $i<100; $i++){
//    $sql = 'ALTER TABLE `yilu_uc`.`user_identity_'.$i.'` CHANGE COLUMN `type` `type` char(6) NOT NULL COMMENT \'身份类型，如：INNER表示内部账号(包括邮箱、用户名、手机号)，微信公众号WX，QQ，ALIPAY\'';
//    $stmt = $app->mysql()->prepare($sql);
//    $stmt->execute();
//}

return_result('dashboard');