<?php
/**
 * @group 用户
 * @name 检查微信用户是否已经扫码登录
 * @desc 微信公众平台（服务号）扫码登录时，前端轮循调用此接口，检查用户是否已经扫码登录
 * @method POST
 * @uri /sign/wechat_check_qr_login
 * @return json
 * {
 *      code: 0
 *      ,data: [
 *          nickname:"Jim"
 *      ]
 *      ,msg: "登录成功"
 * }
 * @exception
 *  0 登录成功【新用户，提醒用户去绑定账号】
 *  1 登录成功【老用户，直接做跳转处理】
 *  2 二维码已失效，点击刷新二维码
 *  3 已扫码，正在登录
 *  4 等待扫码
 *  5 登录成功【已经是登录状态】
 */

$for_bind = input::I()->get_int('for_bind', null);
if (!$for_bind && !empty($self_info['nickname'])){
    return json(5, '登录成功', ['nickname'=>$self_info['nickname']]);
}

$code = isset($_SESSION['weixin_qr_login_code']) ? $_SESSION['weixin_qr_login_code'] : null;
if(!$code || !redis_y::I()->exists(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code)){
    return code(2,'二维码已失效，点击刷新二维码');
}

$data = redis_y::I()->get(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code);
$data = json_decode($data, true);
switch ($data['status']){
    case 'scanned':
        unset($data, $code);
        return json(3, '已扫码，正在登录');
        break;
    case 'login':
        redis_y::I()->del(REDIS_KEY_WEIXIN_QR_LOGIN_CODE.$code);
        //如果是登录用户绑定第三方账号，走此流程
        if ($for_bind){
            logic_user::I()->bind_outer_account('WX', $data['openid'], true);
        }
        //如果是老用户，跳转页面
        if($uid = model_user_identity::I()->find_uid_by_identity('WX', $data['openid'])) {
            //登录用户
            $user_info = logic_user::I()->login_by_uid($uid);
            $nickname = $user_info['nickname'];
            unset($data, $code, $user_info);
            return json(1, '登录成功', ['nickname'=>$nickname]);
        }
        //如果是新用户，提醒绑定账号
        else {
            unset($data['status']);
            //存入SESSION
            $_SESSION['temp_user_info'] = json_encode($data);
            $nickname = $data['nickname'];
            unset($data, $code);
            return json(CODE_SUCCESS, '登录成功', ['nickname'=>$nickname]);
        }
        break;
    default:
    case '0':
        unset($data, $code);
        return json(4, '等待扫码');
        break;
}