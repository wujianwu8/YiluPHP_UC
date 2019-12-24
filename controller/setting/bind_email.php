<?php
/**
 * @name 绑定登录邮箱页面
 * @desc
 * @method GET
 * @uri /setting/bind_email
 * @return HTML
 */

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

return_result('setting/bind_email',
    [
        'email' => $email,
    ]
);