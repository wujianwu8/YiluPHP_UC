<?php
/**
 * @group 内部接口
 * @name 根据vk检查用户是否登录
 * @desc 如果登录了则返回用户的简短信息
 * @method GET|POST
 * @uri /internal/check_login_by_vk
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param integer uid 用户ID 必选 用户的ID，即uid
 * @param integer keep_alive 是否保活 可选 0不延长用户登录时效，1为延长用户登录时效，默认为不延长
 * @return JSON
 * {
 *  code: 0, //未登录-1，0已经登录
 *  msg: "已经登录",
 *  data: {
 *      user_info: {
 *          uid: 123,
 *          nickname: "Jim",
 *          avatar: "https://yiluphp...png",
 *          last_active: 1514567890
 *      }
 *  }
 * }
 * @exception
 *  -1 未登录
 *   0 已登录
 *   1 uid参数错误
 *   2 keep_alive参数错误
 */

$params = input::I()->validate(
    [
        'uid' => 'required|integer|min:1|return',
        'keep_alive' => 'integer|min:0|max:1|return',
    ],
    [
        'uid.*' => 'uid参数错误',
        'keep_alive.*' => 'keep_alive参数错误', //是否延长登录的有效期
    ],
    [
        'uid.*' => 1,
        'keep_alive.*' => 2,
    ]);

if($user_info = logic_user::I()->get_login_user_info_by_uid($params['uid'])){
    if (!empty($params['keep_alive'])){
        $time = time();
        //延长登录状态的有效期，5分钟内只更新一次
        if (isset($user_info['last_active']) && $time-$user_info['last_active']>300){
            logic_user::I()->keep_login_user_alive($user_info['uid'], isset($user_info['vk']) ? $user_info['vk'] : '');
        }
        $user_info['keep_alive'] = $time;
        unset($time);
    }
    unset($params);
    return json(0, YiluPHP::I()->lang('already_logged_in'),
        [
            'user_info' => $user_info,
        ]
    );
}
unset($user_info, $params);
return json(-1, YiluPHP::I()->lang('not_logged_in'));