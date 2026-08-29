<?php
/**
 * @group 用户
 * @name 获取当前登录用户的详细信息
 * @desc
 * @method GET
 * @uri /setting/user_info
 * @return HTML|JSON
 */

$user_info = model_user::I()->find_table(['uid'=>$self_info['uid']],'*',$self_info['uid']);
$has_login_password = !empty($user_info['password']) && !empty($user_info['salt']);
$identity = model_user_identity::I()->select_all(['uid'=>$self_info['uid']], '', 'type,identity', $self_info['uid']);
foreach ($identity as $item){
    if ($item['type']=='INNER'){
        $user_info[logic_user::I()->get_identity_type($item['identity'])] = $item['identity'];
    }
    else{
        $user_info[$item['type']] = $item['identity'];
    }
}
unset($identity, $item, $user_info['password'], $user_info['salt']);
return result('setting/user_info',
    [
        'country_lang_keys' => lib_address::I()->selectCountryLangKeys(),
        'area_list' => lib_ip::I()->getAutoAreaList(),
        'has_login_password' => $has_login_password,
    ]
);
