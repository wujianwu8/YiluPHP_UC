<?php
/**
 * @group 用户
 * @name 发送邮箱验证码
 * @desc 发送邮箱验证码
 * @method POST
 * @uri /send_email_code
 * @param email email Email 邮箱地址,需要rsa_encrypt加密
 * @param string use_for 验证码用处 可选参数,默认为用于注册,find_password为用于找回密码，bind_account用于绑定账号以便登录使用
 * @return json
 * {
 *      code: 1
 *      ,data: []
 *      ,msg: "手机号码填写有误"
 * }
 * @exception
 *  0 发送成功
 *  1 邮箱填写有误
 *  3 您发送验证码太频繁了【超过单个邮箱30秒一次的限制】
 *  4 您发送验证码太频繁了【超过同一个客户端用户30秒一次的限制】
 *  5 您今天发送验证码太多了【24小时内同一个邮箱只能发20次验证码】
 *  6 该邮箱未注册账号
 *  7 该邮箱未注册账号
 *  8 该账号已经被锁住,无法找回密码
 *  9 该邮箱已经注册账号,不可以再绑定到其它账号
 */

$params = input::I()->validate(
    [
        'email' => 'required|email|min:8|max:100|rsa_encrypt|return',
    ],
    [
        'email.*' => '邮箱填写有误',
    ],
    [
        'email.*' => 1,
    ]);
$params['email'] = strtolower($params['email']);
$vk = isset($_COOKIE['vk'])?$_COOKIE['vk']:'';
//准备写发送日志
$time = time();
$record = [
    'email' => $params['email'],
    'client_ip' => client_ip(),
    'vk' => $vk,
    'ctime' => $time,
    'mtime' => $time,
];

$email_cache_key = REDIS_KEY_SEND_VERIFY_CODE_ON_EMAIL.md5($params['email']);
//30秒内同一个邮箱只能发一次验证码
if(redis_y::I()->exists($email_cache_key)){
    $record['refuse_reason'] = '30秒内同一个邮箱只能发一次验证码';
    model_email_code_record::I()->insert_table($record);
    unset($params, $vk, $time, $record, $email_cache_key);
    return code(3, '您发送验证码太频繁了');
}
//同一个客户端用户30秒内只能发一次验证码
if($vk && redis_y::I()->exists(REDIS_KEY_SEND_VERIFY_CODE_ON_CLIENT.$vk)){
    $record['refuse_reason'] = '同一个客户端用户30秒内只能发一次验证码';
    model_email_code_record::I()->insert_table($record);
    unset($params, $vk, $time, $record, $email_cache_key);
    return code(4, '您发送验证码太频繁了');
}

//24小时内同一个邮箱只能发20次验证码
if (model_email_code_record::I()->count( ['email'=>$params['email'], 'ctime'=>['symbol'=>'>=', 'value'=>$time-TIME_DAY]]) > 20){
    $record['refuse_reason'] = '24小时内同一个邮箱只能发20次验证码';
    model_email_code_record::I()->insert_table($record);
    unset($params, $vk, $time, $record, $mobile_cache_key);
    return code(5, '您今天发送验证码太多了');
}

$use_for = input::I()->post_trim('use_for');

//用于绑定账号以便登录使用
if($use_for=='bind_account') {
    //检查是否登录
    if (empty($self_info)){
        unset($params, $vk, $time, $record, $mobile_cache_key, $use_for);
        return code(-1, '请先登录');
    }
    //检查是不是与现在绑定的邮箱一样的
    $idendidy = model_user_identity::I()->select_all(['uid'=>$self_info['uid']], '', 'type,identity', $self_info['uid']);
    $email = '';
    foreach ($idendidy as $item){
        if ($item['type']=='INNER'){
            if('email' == logic_user::I()->get_identity_type($item['identity'])){
                $email = $item['identity'];
            }
        }
    }
    unset($idendidy, $item);
    if ($email == $params['email']){
        $record['refuse_reason'] = '该邮箱与现在绑定的登录邮箱一样，不需要修改';
        model_email_code_record::I()->insert_table($record);
        unset($params, $vk, $time, $record, $mobile_cache_key, $use_for, $email);
        return code(10, '该邮箱与现在绑定的登录邮箱一样，不需要修改');
    }

    if ($uid = model_user_identity::I()->find_uid_by_identity('INNER', $params['email'])) {
        $record['refuse_reason'] = '该邮箱已经注册账号,不可以再绑定到其它账号';
        model_email_code_record::I()->insert_table($record);
        unset($params, $vk, $time, $record, $mobile_cache_key, $use_for, $email, $uid);
        return code(9, '该邮箱已经注册账号,不可以再绑定到其它账号');
    }
}
//用于找回密码
if($use_for=='find_password'){
    if(!$uid = model_user_identity::I()->find_uid_by_identity('INNER', $params['email'])) {
        $record['refuse_reason'] = '该邮箱未注册账号,找不到uid';
        model_email_code_record::I()->insert_table($record);
        unset($params, $vk, $time, $record, $mobile_cache_key, $use_for, $uid);
        return code(6, '该邮箱未注册账号');
    }
    if(!$user_info = model_user::I()->find_table(['uid'=>$uid], 'status', $uid)){
        $record['refuse_reason'] = '该邮箱未注册账号,找不到用户信息,uid='.$uid;
        model_email_code_record::I()->insert_table($record);
        unset($params, $vk, $time, $record, $mobile_cache_key, $use_for, $uid, $user_info);
        return code(7, '该邮箱未注册账号');
    }
    if(empty($user_info['status'])) {
        $record['refuse_reason'] = '该账号已经被锁住,无法找回密码,uid='.$uid;
        model_email_code_record::I()->insert_table($record);
        unset($params, $vk, $time, $record, $mobile_cache_key, $use_for, $uid, $user_info);
        return code(8, '该账号已经被锁住,无法找回密码');
    }
    unset($uid, $user_info);
}

$code_cache_key = REDIS_KEY_EMAIL_VERIFY_CODE.md5($params['email'].'_'.session_id());
//检查该手机号是否还有验证码未使用，如果有则不变
if(!$code = redis_y::I()->get($code_cache_key)){
    //没有则生成新的验证码
    $code = rand_string(6);
}

//调用第三方平台进行短信发送
$subject = YiluPHP::I()->lang('email_verify_code_subject');
$body = YiluPHP::I()->lang('email_verify_code_body', ['code'=>$code]);

//调用队列发验证码
add_to_queue('send_email_code', [
    'to_alias' => $params['email'],
    'to_email' => $params['email'],
    'subject' => $subject,
    'html_body' => $body,
]);

//发送成功则保存到redis中
redis_y::I()->set($code_cache_key, $code);
redis_y::I()->expire($code_cache_key, TIME_10_MIN);

//标记该手机号已经发过验证码
redis_y::I()->set($email_cache_key, 1);
redis_y::I()->expire($email_cache_key, TIME_30_SEC);

//标记该客户端已经发过验证码
if($vk){
    redis_y::I()->set(REDIS_KEY_SEND_VERIFY_CODE_ON_CLIENT.$vk, 1);
    redis_y::I()->expire(REDIS_KEY_SEND_VERIFY_CODE_ON_CLIENT.$vk, TIME_30_SEC);
}

$record['is_send'] = 1;
model_email_code_record::I()->insert_table($record);
unset($params, $vk, $time, $record, $subject, $body, $email_cache_key, $code_cache_key);
return json(0,'发送成功', ['code'=>$code]);