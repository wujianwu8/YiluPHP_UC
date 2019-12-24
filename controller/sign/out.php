<?php
/**
 * @name 用户退出登录
 * @desc
 * @method GET
 * @uri /sign/out
 * @return 直接跳转到登录页或json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "退出成功"
 * }
 * @exception
 *  0 退出成功
 */

$app->logic_user->destroy_login_session();
if( isset($_REQUEST['dtype']) && in_array(strtolower($_REQUEST['dtype']), ['json', 'jsonp']) ){
    if(strtolower($_REQUEST['dtype'])=='jsonp'){
        return_jsonp(CODE_SUCCESS, $app->lang('sign_out_successfully'));
    }
    else{
        return_json(CODE_SUCCESS, $app->lang('sign_out_successfully'));
    }
}
$redirect_uri = '/sign/in';
if (!empty($_GET['redirect_uri'])){
    $tmp = trim($_GET['redirect_uri']);
    if ($tmp!=''){
        $redirect_uri = $tmp;
    }
}
if ($redirect_uri == '/sign/in'){
    //再次登录后需要跳转到的uri
    if (!empty($_GET['after_login_uri'])){
        $tmp = trim($_GET['after_login_uri']);
        if ($tmp!=''){
            $redirect_uri .= '?redirect_uri='.$tmp;
        }
    }
}
header('Location: '.$redirect_uri);
exit;
