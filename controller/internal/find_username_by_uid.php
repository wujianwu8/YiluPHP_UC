<?php
/**
 * @name 根据用户ID获取username
 * @desc
 * @method GET|POST
 * @uri /internal/find_username_by_uid
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param integer uid 用户ID 必选 用户的ID，即uid
 * @return JSON
 * {
 *  code: 0, //0获取成功
 *  msg: "获取成功",
 *  data: {
 *      username: "jimwu"
 *  }
 * }
 * @exception
 *  0 获取成功
 *  1 uid参数错误
 *  2 用户不存在
 */

$params = $app->input->validate(
    [
        'uid' => 'required|integer|min:1|return',
    ],
    [
        'uid.*' => 'uid参数错误',
    ],
    [
        'uid.*' => 1,
    ]);

if (!$identity_list = $app->model_user_identity->select_all(
    ['type'=>'INNER', 'uid'=>$params['uid']],
    '', 'identity', $params['uid']
)){
    unset($params, $identity_list);
    return_code(2, $app->lang('user_not_exist'));
}
unset($params);
$username = null;
foreach ($identity_list as $item){
    $identity = $item['identity'];
    $type = $app->logic_user->get_identity_type($identity);
    if ($type=='username'){
        $username = $identity;
        break;
    }
    unset($identity, $type);
}
if (!$username){
    unset($params, $username);
    return_code(3, $app->lang('user_not_exist'));
}
return_json(0, $app->lang('successful_get'),['username'=>$username]);