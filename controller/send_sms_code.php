<?php
/**
 * @group 用户
 * @name 发送短信验证码
 * @desc 发送短信验证码
 * @method POST
 * @uri /send_sms_code
 * @param integer area_code 地区编号 5位以内的纯数字
 * @param integer mobile 手机号 20位以内的纯数字
 * @param string use_for 验证码用处 可选参数,默认为用于注册,find_password为用于找回密码
 * @return json
 * {
 *      code: 2
 *      ,data: []
 *      ,msg: "手机号码填写有误"
 * }
 * @exception
 *  0 发送成功
 *  1 手机归属地有误
 *  2 手机号码填写有误
 *  3 您发送验证码太频繁了【超过单个手机号30秒一次的限制】
 *  4 您发送验证码太频繁了【超过同一个客户端用户30秒一次的限制】
 *  5 您今天发送验证码太多了【24小时内同一个手机号只能发20次验证码】
 *  6 该手机号未注册账号
 *  7 该手机号未注册账号
 *  8 该账号已经被锁住,无法找回密码
 *  9 该手机号已经注册过账号，请直接登录，或者使用找回密码功能
 *  10 use_for参数错误
 *  11 您发送验证码太频繁了【同一个ip5秒内只能发送3次验证码】
 *  12 您今天发送验证码太多了【同一个ip24小时内只能发送100次验证码】
 */

$params = input::I()->validate(
    [
        'area_code' => 'required|integer|min:1|max:9999|return',
        'mobile' => 'required|integer|min:100000|max:99999999999|rsa_encrypt|return',
        'use_for' => 'required|string|return',
    ],
    [
        'area_code.*' => '手机归属地有误',
        'mobile.*' => '手机号码填写有误',
        'use_for.*' => 'use_for参数错误',
    ],
    [
        'area_code.*' => 1,
        'mobile.*' => 2,
        'use_for.*' => 10,
    ]);
if (!in_array($params['use_for'], ['sign_up', 'find_password', 'bind_account'])){
    return code(10, 'use_for参数错误');
}

$phone = $params['area_code'] .'-'. $params['mobile'];
$complete_phone = $params['area_code'] . $params['mobile'];

$vk = isset($_COOKIE['vk'])?$_COOKIE['vk']:'';
//准备写发送日志
$time = time();
$client_ip = client_ip();
$record = [
    'area_code' => $params['area_code'],
    'mobile' => $params['mobile'],
    'client_ip' => $client_ip,
    'vk' => $vk,
    'ctime' => $time,
    'mtime' => $time,
];
if ($vk){
    $vk = md5($vk);
}

$mobile_cache_key = REDIS_KEY_SEND_VERIFY_CODE_ON_MOBILE.$phone;
//30秒内同一个手机号只能发一次验证码
if(redis_y::I()->exists($mobile_cache_key)){
    $record['refuse_reason'] = '30秒内同一个手机号只能发一次验证码';
    model_sms_record::I()->insert_table($record);
    unset($params, $vk, $time, $record, $mobile_cache_key, $client_ip);
    return code(3, '您发送验证码太频繁了');
}
//同一个客户端用户30秒内同一个手机号只能发一次验证码
if($vk && redis_y::I()->exists(REDIS_KEY_SEND_VERIFY_CODE_ON_CLIENT.$vk)){
    $record['refuse_reason'] = '同一个客户端用户30秒内只能发一次验证码';
    model_sms_record::I()->insert_table($record);
    unset($params, $vk, $time, $record, $mobile_cache_key, $client_ip);
    return code(4, '您发送验证码太频繁了');
}

//24小时内同一个手机号只能发20次验证码
if (model_sms_record::I()->count( ['area_code'=>$params['area_code'], 'mobile'=>$params['mobile'], 'ctime'=>['symbol'=>'>=', 'value'=>$time-TIME_DAY]]) > 20){
    $record['refuse_reason'] = '24小时内同一个手机号只能发20次验证码';
    model_sms_record::I()->insert_table($record);
    unset($params, $vk, $time, $record, $mobile_cache_key, $client_ip);
    return code(5, '您今天发送验证码太多了');
}

//同一个ip5秒内只能发送3次验证码
$key = REDIS_KEY_SEND_SMS_CODE_ON_IP_5SEC.md5($client_ip);
$count = redis_y::I()->incr($key);
if ($count==1) {
    redis_y::I()->expire($key, 5);
}
if ($count>3){
    $record['refuse_reason'] = '同一个ip5秒内只能发送3次验证码';
    model_sms_record::I()->insert_table($record);
    unset($params, $vk, $time, $record, $mobile_cache_key, $key, $count, $client_ip);
    return code(11, '您发送验证码太频繁了');
}
//同一个ip24小时内只能发送100次验证码
$key = REDIS_KEY_SEND_SMS_CODE_ON_IP_DAY.md5($client_ip);
$count = redis_y::I()->incr($key);
if ($count==1) {
    redis_y::I()->expire($key, TIME_DAY);
}
if ($count>100){
    $record['refuse_reason'] = '同一个ip24小时内只能发送100次验证码';
    model_sms_record::I()->insert_table($record);
    unset($params, $vk, $time, $record, $mobile_cache_key, $key, $count, $client_ip);
    return code(12, '您今天发送验证码太多了');
}

//用于注册
if(in_array($params['use_for'], ['sign_up','bind_account'])){
    if($uid = model_user_identity::I()->find_uid_by_identity('INNER', $phone)) {
        $record['refuse_reason'] = '该手机号已经注册过账号：'.$uid;
        model_sms_record::I()->insert_table($record);
        unset($params, $vk, $time, $record, $mobile_cache_key, $uid);
        return code(9, '该手机号已经注册过账号，请直接登录，或者使用找回密码功能');
    }
}

//用于找回密码
if($params['use_for']=='find_password'){
    if(!$uid = model_user_identity::I()->find_uid_by_identity('INNER', $phone)) {
        $record['refuse_reason'] = '该手机号未注册账号,找不到uid';
        model_sms_record::I()->insert_table($record);
        unset($params, $vk, $time, $record, $mobile_cache_key, $uid);
        return code(6, '该手机号未注册账号');
    }
    if(!$user_info = model_user::I()->find_table(['uid'=>$uid], 'status', $uid)){
        $record['refuse_reason'] = '该手机号未注册账号,找不到用户信息,uid='.$uid;
        model_sms_record::I()->insert_table($record);
        unset($params, $vk, $time, $record, $mobile_cache_key, $uid, $user_info);
        return code(7, '该手机号未注册账号');
    }
    if(empty($user_info['status'])) {
        $record['refuse_reason'] = '该账号已经被锁住,无法找回密码,uid='.$uid;
        model_sms_record::I()->insert_table($record);
        unset($params, $vk, $time, $record, $mobile_cache_key, $uid, $user_info);
        return code(8, '该账号已经被锁住,无法找回密码');
    }
    unset($uid, $user_info);
}

$code_cache_key = REDIS_KEY_MOBILE_VERIFY_CODE.md5($phone.'_'.session_id());
//检查该手机号是否还有验证码未使用，如果有则不变
if(!$code = redis_y::I()->get($code_cache_key)){
    //没有则生成新的验证码
    $code = mt_rand(1000, 9999);
}

//调用第三方平台进行短信发送
$message = YiluPHP::I()->lang('register_sms_verify_code', ['code'=>$code]);
$lang = YiluPHP::I()->current_lang();
$lang = strtolower($lang);
!in_array($lang, ['zh','en']) && $lang='zh';
$template_code = $GLOBALS['config']['sms']['aliyun']['template_code_'.$lang];
$sign_name = $GLOBALS['config']['sms']['aliyun']['sign_name_'.$lang];
$template_param = ['code'=>$code];

//调用队列发验证码
add_to_queue('send_sms_code', [
    'area_code' => $params['area_code'],
    'mobile' => $params['mobile'],
    'message' => $message,
    'template_code' => $template_code,
    'sign_name' => $sign_name,
    'template_param' => $template_param,
]);

//发送成功则保存到redis中
redis_y::I()->set($code_cache_key, $code);
redis_y::I()->expire($code_cache_key, TIME_10_MIN);

//标记该手机号已经发过验证码
redis_y::I()->set($mobile_cache_key, 1);
redis_y::I()->expire($mobile_cache_key, TIME_30_SEC);

//标记该客户端已经发过验证码
if($vk){
    redis_y::I()->set(REDIS_KEY_SEND_VERIFY_CODE_ON_CLIENT.$vk, 1);
    redis_y::I()->expire(REDIS_KEY_SEND_VERIFY_CODE_ON_CLIENT.$vk, TIME_30_SEC);
}

$record['is_send'] = 1;
model_sms_record::I()->insert_table($record);
unset($params, $vk, $time, $record, $message, $mobile_cache_key, $code_cache_key, $template_code, $sign_name, $template_param, $lang);
//return json(0,'发送成功', ['code'=>$code]);
return json(0,'发送成功');