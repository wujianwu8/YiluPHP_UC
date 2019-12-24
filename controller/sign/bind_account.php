<?php
/**
 * @name 第三方平台登录后绑定账号（UI界面）
 * @desc
 * @method GET
 * @uri /sign/bind_account
 * @return HTML
 */

if(empty($_SESSION['temp_user_info'])){
    header('location: /');
    //未做第三方登录
    return_code(-1, '请先登录');
}
$data = $_SESSION['temp_user_info'];
$data = json_decode($data, true);

if ($data['identity_type']=='QQ'){
    if (empty($app->redis()->get(REDIS_KEY_QQ_CALLBACK.$data['openid']))) {
        $app->redis()->set(REDIS_KEY_QQ_CALLBACK.$data['openid'], 1);
        $app->redis()->expire(REDIS_KEY_QQ_CALLBACK.$data['openid'], 10);

        echo '<script>
        if(null != window.opener){
            window.opener.location.href = document.location.href;
            window.close();
        }
        else{
            window.location.reload();
        }
    </script>';
        exit;
    }
}

//如果用户已经注册,则直接跳转
if($uid = $app->model_user_identity->find_uid_by_identity($data['identity_type'], $data['openid'])) {
    //登录用户
    $user_info = $app->logic_user->login_by_uid($uid);
    $app->logic_user->auto_jump(false, $user_info['tlt']);
}

$plat_list = [
    'WX' => '微信',
    'QQ' => 'QQ',
    'LINKEDIN' => '领英',
    'ALIPAY' => '支付宝',
];
$params = [
    'nickname' => $data['nickname'],
    'avatar' => $data['avatar'],
    'from_plat' => $plat_list[$data['identity_type']],
    'area_list' => $app->lib_ip->getAutoAreaList(),
];
unset($data,$plat_list);
return_result('bind_account', $params);
