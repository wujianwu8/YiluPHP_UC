<?php
/**
 * @group 用户
 * @name 用户登录
 * @desc 如果是从第三方授权登录过来的，则会登录后绑定
 * @method POST
 * @uri /sign/login
 * @param integer area_code 地区编号 必选 5位以内的纯数字
 * @param string identity 手机号 必选 经过RSA公钥加密后的用户身份
 * @param string password 密码 必选 经过RSA公钥加密后的密码字符串
 * @param integer verify_code 手机验证码 可选
 * @param integer is_bind 是否绑定第三方 可选 0表示不用绑定第三方账号，1表示需要绑定第三方账号（即通过授权登录的用户），默认为0
 * @return json
 * {
 *      code: 0
 *      ,data: [
 *          'redirect_uri':'http://www.yiluphp.com/sss..fff.html' //跳转的链接地址
 *      ]
 *      ,msg: "登录成功"
 * }
 * @exception
 *  0 登录成功
 *  1 请先发送手机验证码
 *  2 手机号码填写有误
 *  3 验证码错误
 *  4 此手机号已注册
 *  5 授权登录已失效，请重新授权登录
 *  6 授权登录已失效，请重新授权登录
 *  7 "{outer nickname}"已经绑定其它账户,不能再绑定
 *  8 "{nickname}"已经绑定其它{第三方平台名}账户,不能再绑定
 *  10 手机归属地有误
 *  11 手机号码填写有误
 *  12 密码不正确
 *  13 记住登录状态的参数错误
 */

$params = $app->input->validate(
    [
        'area_code' => 'integer|min:1|max:9999|return',
        'identity' => 'required|trim|string|min:2|max:100|rsa_encrypt|return',
        'password' => 'required|trim|string|min:6|max:32|rsa_encrypt|return',
        'remember_me' => 'integer|min:0|max:1|return',
    ],
    [
        'area_code.*' => $app->lang('wrong_area_code_of_mobile'),
        'identity.*' => $app->lang('login_account_error'),
        'password.*' => $app->lang('password_error'),
        'remember_me.*' => $app->lang('stay_logged_in_param_error'),
    ],
    [
        'area_code.*' => 10,
        'mobile.*' => 11,
        'password.*' => 12,
        'remember_me.*' => 13,
    ]);

$params['identity'] = strtolower($params['identity']);
$type = $app->logic_user->get_identity_type($params['identity']);
if($type=='mobile'){
    if( empty($params['area_code']) ){
        return_code(10, $app->lang('wrong_area_code_of_mobile'));
    }
    $identity = $params['area_code'].'-'.$params['identity'];
}
else{
    $identity = $params['identity'];
}

//检查此登录账号有没有注册过
if(!$uid = $app->model_user_identity->find_uid_by_identity('INNER', $identity)){
    unset($identity, $params, $type, $uid);
    return_code(1, $app->lang('login_account_does_not_exist'));
}
//校验密码
if(!$user_info = $app->model_user->find_table(['uid'=>$uid], '*', $uid)){
    unset($identity, $params, $type, $uid, $user_info);
    return_code(2, $app->lang('login_account_does_not_exist'));
}

if(md5($params['password'].$user_info['salt']) !== $user_info['password']){
    unset($identity, $params, $type, $uid, $user_info);
    return_code(3, $app->lang('password_error'));
}

$repair_info = [];
//查看SESSION中是否有从第三方登录过来的使用信息
//用于判断是否要绑定账号
$is_bind = $app->input->post_int('is_bind',0);
if($is_bind===1){
    if(!isset($_SESSION['temp_user_info'])){
        unset($params, $type, $uid, $user_info, $is_bind);
        return_code(5, $app->lang('authorized_login_has_expired'));
    }
    $temp_user_info = $_SESSION['temp_user_info'];
    $temp_user_info = json_decode($temp_user_info, true);
    if(empty($temp_user_info['identity_type'])){
        unset($params, $type, $uid, $user_info, $is_bind, $temp_user_info);
        return_code(6, $app->lang('authorized_login_has_expired'));
    }

    //检查此外部账户是否已经绑定过用户
    $check_uid = $app->model_user_identity->find_uid_by_identity($temp_user_info['identity_type'], $temp_user_info['openid']);
    if($check_uid && $check_uid!==$uid){
        unset($identity, $params, $type, $uid, $check_uid, $user_info);
        return_code(7, $app->lang('nickname_have_bound_to_other_account', [
            'nickname' => $temp_user_info['nickname']
        ]));
    }
    if(!($check_uid && $check_uid==$uid)) {
        //检查当前内部用户是否已经绑定过相同类型的外部账户
        if($app->model_user_identity->find_table(
            [
                'uid' => $uid,
                'type' => $temp_user_info['identity_type']
            ],
            'uid', $uid)){
            unset($identity, $params, $type, $uid, $check_uid);
            return_code(8, $app->lang('you_have_bound_other_outer_account', [
                'outer_account' => $app->lang('identity_type_user_'.$temp_user_info['identity_type'])
            ]));
        }
    }

    if(!$check_uid) {
        $identity_plat = [
            'uid' => $uid,
            'type' => $temp_user_info['identity_type'],
            'identity' => $temp_user_info['openid'],
            'ctime' => time(),
        ];
        isset($temp_user_info['access_token']) && $identity_plat['access_token'] = $temp_user_info['access_token'];
        isset($temp_user_info['expires_at']) && $identity_plat['expires_at'] = $temp_user_info['expires_at'];
        isset($temp_user_info['refresh_token']) && $identity_plat['refresh_token'] = $temp_user_info['refresh_token'];

        isset($temp_user_info['gender']) && empty($user_info['gender']) && $repair_info['gender'] = $temp_user_info['gender'];
        isset($temp_user_info['birthday']) && empty($user_info['birthday']) && $repair_info['birthday'] = $temp_user_info['birthday'];
        isset($temp_user_info['avatar']) && empty($user_info['avatar']) && $repair_info['avatar'] = $temp_user_info['avatar'];
        isset($temp_user_info['country']) && empty($user_info['country']) && $repair_info['country'] = $temp_user_info['country'];
        isset($temp_user_info['province']) && empty($user_info['province']) && $repair_info['province'] = $temp_user_info['province'];
        isset($temp_user_info['city']) && empty($user_info['city']) && $repair_info['city'] = $temp_user_info['city'];

        $app->model_user_identity->insert_identity($identity_plat);
        unset($identity_plat);
    }
    unset($check_uid, $temp_user_info);
}

if($repair_info) {
    $where = ['uid' => $uid];
    $app->model_user->update_table($where, $repair_info);
    unset($where);
}

$remember_me = $app->input->post_int('remember_me',false);
//登录用户
$app->logic_user->create_login_session($user_info, !empty($remember_me));
$tlt = $user_info['tlt'];

unset($user_info, $is_bind, $params, $identity, $uid, $repair_info);
//返回结果
return_json(0,$app->lang('login_succeed'), [
    'redirect_uri' => $app->logic_user->auto_jump(true, $tlt)
]);
