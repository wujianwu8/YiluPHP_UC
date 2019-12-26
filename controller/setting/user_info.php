<?php
/**
 * @group 用户
 * @name 账号设置
 * @desc 修改自己的资料、绑定邮箱、登录账号
 * @method GET
 * @uri /setting/user_info
 * @return HTML
 */

$user_info = $app->model_user->find_table(['uid'=>$self_info['uid']]);
$identity = $app->model_user_identity->select_all(['uid'=>$self_info['uid']], '', 'type,identity', $self_info['uid']);
foreach ($identity as $item){
    if ($item['type']=='INNER'){
        $user_info[$app->logic_user->get_identity_type($item['identity'])] = $item['identity'];
    }
    else{
        $user_info[$item['type']] = $item['identity'];
    }
}
unset($identity, $item, $user_info['password'], $user_info['salt']);

return_result('setting/user_info',
    [
        'user_info' => $user_info,
        'country_lang_keys' => $app->lib_address->selectCountryLangKeys(),
    ]
);