<?php
/**
 * @group 用户
 * @name 微信公众平台（服务号）登录时，二维码中的链接就是这个地址
 * @desc 用户用微信扫码后，从此链接跳去微信授权登录
 * @method GET
 * @uri /sign/wechat_login_by_qr
 * @param string code 用户唯一识别码 必选 识别码在生成二维码时生成，用户扫码授权登录后，根据这个识别码与用户的客户端做对应
 * @return 跳转到微信进行授权登录
 */

$code = input::I()->get_trim('code');
if(!redis_y::I()->exists(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code)){
    return code(CODE_UNDEFINED_ERROR_TYPE,'二维码已失效，请刷新二维码');
}
$data = redis_y::I()->get(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code);
$data = json_decode($data, true);
$data['status'] = 'scanned';
redis_y::I()->set(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code, json_encode($data));
//延长二维码的有效期
redis_y::I()->expire(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code, TIME_MIN);

//session中记录传过来的code
$_SESSION['weixin_qr_login_code'] = $code;
$state = 'weixin_qr_'.md5(uniqid(rand(), TRUE).microtime().uniqid());
write_applog('DEBUG', json_encode($_COOKIE));
oauth_wechat::I()->login('snsapi_userinfo', $state);
