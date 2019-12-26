<?php
/**
 * @group 用户
 * @name 保存登录邮箱
 * @desc
 * @method POST
 * @uri /setting/save_info
 * @param string email 邮箱 必选 经过RAS公钥加密后的邮箱字符串
 * @param string verify_code 验证码 必选
 * @param string password 登录密码 必选 经过RAS公钥加密后的密码字符串
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "绑定成功"
 * }
 * @exception
 *  0 绑定成功
 *  1 绑定失败
 *  2 邮箱错误
 *  3 验证码错误
 *  4 密码错误
 *  5 邮箱错误
 *  6 验证码错误或已失效
 *  7 验证码错误
 *  8 密码错误
 *  9 该邮箱与现在绑定的登录邮箱一样，不需要修改
 *  10 该邮箱已经注册账号,不可以再绑定到其它账号
 */

$params = $app->input->validate(
    [
        'email' => 'required|string|min:7|rsa_encrypt|return',
        'verify_code' => 'required|trim|string|min:4|return',
        'password' => 'required|trim|string|min:6|max:32|rsa_encrypt|return',
    ],
    [
        'email' => '邮箱错误',
        'verify_code' => '验证码错误',
        'password' => '密码错误',
    ],
    [
        'email' => 2,
        'verify_code' => 3,
        'password' => 4,
    ]);
//检查操作权限

if (!is_email($params['email'])){
    unset($params);
    return_code(5, '邮箱错误');
}
$params['email'] = strtolower($params['email']);

$code_cache_key = REDIS_KEY_EMAIL_VERIFY_CODE.md5($params['email'].'_'.session_id());
//检查验证码是否正确
if(!$cache_code = $app->redis()->get($code_cache_key)){
    unset($code_cache_key, $params, $cache_code);
    return_code(6,'验证码错误或已失效');
}
if (strtolower($cache_code)!=strtolower($params['verify_code'])){
    unset($code_cache_key, $params, $cache_code);
    return_code(7,'验证码错误');
}

//检查密码是否正确
$user_info = $app->model_user->find_table(['uid'=>$self_info['uid']], '*', $self_info['uid']);
if (!$user_info || md5($params['password'].$user_info['salt'])!=$user_info['password']){
    unset($code_cache_key, $params, $cache_code, $user_info);
    return_code(8,'密码错误');
}
$identity = $app->model_user_identity->select_all(['uid'=>$self_info['uid']], '', 'type,identity', $self_info['uid']);
$email = '';
foreach ($identity as $item){
    if ($item['type']=='INNER'){
        if('email' == $app->logic_user->get_identity_type($item['identity'])){
            $email = $item['identity'];
        }
    }
}
unset($identity, $item);
if ($email == $params['email']){
    unset($code_cache_key, $params, $cache_code, $user_info, $email);
    return_code(9, '该邮箱与现在绑定的登录邮箱一样，不需要修改');
}

if ($uid = $app->model_user_identity->find_uid_by_identity('INNER', $params['email'])) {
    unset($code_cache_key, $params, $cache_code, $user_info, $email, $uid);
    return_code(10, '该邮箱已经注册账号,不可以再绑定到其它账号');
}

//删除原来登录邮箱
if ($email != ''){
    $app->model_user_identity->delete_identity('INNER', $email, $self_info['uid']);
}

$data = [
    'uid'=>$self_info['uid'],
    'type'=>'INNER',
    'identity'=>$params['email'],
];
if(!$app->model_user_identity->insert_identity($data)){
    unset($code_cache_key, $params, $cache_code, $user_info, $email, $uid);
    return_json(1,'绑定失败');
}
unset($code_cache_key, $params, $cache_code, $user_info, $email, $uid);
//返回结果
return_json(CODE_SUCCESS,'绑定成功');
