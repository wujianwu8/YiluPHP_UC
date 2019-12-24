<?php
/**
 * @name 根据uid获取该用户能看到的所有菜单
 * @desc
 * @method GET|POST
 * @uri /internal/select_menu_list
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param integer uid 用户ID 必选 用户的ID，即uid
 * @return JSON
 * {
 *  code: 0, //0获取成功，其它不成功
 *  msg: "获取成功",
 *  data: {
 *      user_info: {
 *          uid: 123,
 *          nickname: "Jim",
 *          avatar: "https://yiluphp...png",
 *          gender: "female",
 *          birthday: "2011-08-21",
 *          country: "中国",
 *          province: "江西省",
 *          city: "赣州市",
 *          last_active: 1514567890,
 *          ctime: 1494567890
 *      }
 *  }
 * }
 * @exception
 *  0 获取成功
 *  1 uid参数错误
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

if ($user_info = $app->logic_user->find_user_safe_info($params['uid'])) {
    unset($params);
    return_json(0, $app->lang('successful_get'),
        [
            'user_info' => $user_info,
        ]
    );
}
unset($user_info, $params);
return_json(2, $app->lang('failure_get'));