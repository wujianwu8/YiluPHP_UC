<?php
/**
 * @group 内部接口
 * @name 根据登录账号查找用户
 * @desc 登录账号包括登录名、手机号、邮箱
 * @method GET|POST
 * @uri /internal/find_user_by_identity
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param string identity 登录账号 必选 登录账号，包括登录名、手机号、邮箱，手机号格式为：86-13812345678，如果没有区号，则默认为86
 * @return JSON
 * {
 *  code: 0, //0获取成功
 *  msg: "获取成功",
 *  data: {
 *      uid: 123,
 *      nickname: "Jim",
 *      avatar: "https://.....",
 *  }
 * }
 * @exception
 *  0 获取成功
 *  1 identity参数错误
 *  2 用户不存在
 */

$params = input::I()->validate(
    [
        'identity' => 'required|string|min:1|return',
    ],
    [
        'identity.*' => YiluPHP::I()->lang('parameter_error_xxx', ['field'=>YiluPHP::I()->lang('bind_account')]),
    ],
    [
        'identity.*' => 1,
    ]);

$params['identity'] = strtolower($params['identity']);
$type = logic_user::I()->get_identity_type($params['identity']);
if($type=='mobile'){
    $identity = explode('-',$params['identity']);
    if (count($identity)!=2){
        $identity = '86-'.$params['identity'];
    }
    elseif (empty($identity[0])){
        $identity = '86-'.$identity[1];
    }
}
else{
    $identity = $params['identity'];
}

//检查此登录账号有没有注册过
if(!$uid = model_user_identity::I()->find_uid_by_identity('INNER', $identity)){
    unset($identity, $params, $type, $uid);
    return code(2, YiluPHP::I()->lang('login_account_does_not_exist'));
}

if(!$user_info = logic_user::I()->find_user_safe_info($uid)){
    unset($params, $uid, $user_info);
    return code(3, YiluPHP::I()->lang('user_not_exist'));
}
unset($params);
return json(0, YiluPHP::I()->lang('successful_get'),['uid'=>$uid, 'nickname'=>$user_info['nickname'], 'avatar'=>$user_info['avatar']]);