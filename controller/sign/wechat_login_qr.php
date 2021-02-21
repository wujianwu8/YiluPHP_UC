<?php
/**
 * @group 用户
 * @name 微信公众平台（服务号）登录用的二维码图片
 * @desc 即图片地址
 * @method GET
 * @uri /sign/wecaht_login_qr
 * @param string code 用户唯一识别码 必选 识别码在生成二维码时生成，用户扫码授权登录后，根据这个识别码与用户的客户端做对应
 * @return 跳转到微信进行授权登录
 */

/*
 * 调用方法：QRcode::png('http://www.cnblogs.com/txw1958/');
 * public static function png($text, $outfile=false, $level=QR_ECLEVEL_L, $size=3, $margin=4, $saveandprint=false)
 * 参数$text表示生成二位的的信息文本；
 * 参数$outfile表示是否输出二维码图片 文件，默认否；
 * 参数$level表示容错率，也就是有被覆盖的区域还能识别，分别是 L（QR_ECLEVEL_L，7%），M（QR_ECLEVEL_M，15%），Q（QR_ECLEVEL_Q，25%），H（QR_ECLEVEL_H，30%）；
 * 参数$size表示生成图片大小，默认是3；参数$margin表示二维码周围边框空白区域间距值；
 * 参数$saveandprint表示是否保存二维码并显示。
 * */

$code = md5(uniqid().client_ip().microtime().mt_rand(1,999999).uniqid());
$sid = session_id();
$data = [
    'sid' => $sid,
    'status' => '0',
];
redis_y::I()->set(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code, json_encode($data));
redis_y::I()->expire(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code, TIME_MIN);

$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
$url = $http_type . $_SERVER['HTTP_HOST'].'/sign/wechat_login_by_qr/code/'.$code;

//在session中记录code
$_SESSION['weixin_qr_login_code'] = $code;

QRcode::png($url, false, QR_ECLEVEL_H, 4, 3);