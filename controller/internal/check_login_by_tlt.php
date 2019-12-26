<?php
/**
 * @group 内部接口
 * @name 根据tlt检查用户是否登录
 * @desc 如果登录了则返回用户的安全信息，TLT是临时登录令牌(Temporary login token)，30秒内有效
 * @method GET|POST
 * @uri /internal/check_login_by_tlt
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param string tlt TLT令牌 必选 TLT是临时登录令牌(Temporary login token)，30秒内有效
 * @return JSON
 * {
 *  code: 0, //未登录-1，0已经登录
 *  msg: "已经登录",
 *  data: {
 *      user_info: {
 *          uid: 123,
 *          nickname: "Jim",
 *          avatar: "https://yiluphp...png",
 *          last_active: 1514567890,
 *          gender: "female",
 *          birthday: "2011-08-21",
 *          country: "中国",
 *          province: "江西省",
 *          city: "赣州市",
 *          last_active: 1514567890,
 *          ctime: 1494567890,
 *          client_ip:"127.0.0.1"
 *      }
 *  }
 * }
 * @exception
 *  -1 未登录
 *   0 已登录
 *   1 tlt参数错误
 */

$params = $app->input->validate(
    [
        'tlt' => 'required|trim|string|min:32|max:32|return',
    ],
    [
        'tlt.*' => 'tlt参数错误',
    ],
    [
        'tlt.*' => 1,
    ]);

if($user_info = $app->redis()->get(REDIS_KEY_USER_LOGIN_TLT.$params['tlt'])){
    $user_info = json_decode($user_info, true);
    if($user_info && !empty($user_info['uid'])){
        $client_ip = $user_info['client_ip'];
        if ($user_info = $app->logic_user->find_user_safe_info($user_info['uid'])) {
            $user_info['client_ip'] = $client_ip;
            unset($params, $client_ip);
            return_json(0, $app->lang('already_logged_in'),
                [
                    'user_info' => $user_info,
                ]
            );
        }
    }
}

unset($user_info, $params);
return_json(-1, $app->lang('not_logged_in'));