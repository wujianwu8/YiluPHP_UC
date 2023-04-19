<?php
/**
 * @group 用户
 * @name 绑定登录邮箱页面
 * @desc
 * @method GET
 * @uri /setting/bind_email
 * @return HTML
 */

$identity = model_user_identity::I()->select_all(['uid'=>$self_info['uid']], '', 'type,identity', $self_info['uid']);
$email = '';
foreach ($identity as $item){
    if ($item['type']=='INNER'){
        if('email' == logic_user::I()->get_identity_type($item['identity'])){
            $email = $item['identity'];
        }
    }
}
unset($identity, $item);

return result('setting/bind_email');