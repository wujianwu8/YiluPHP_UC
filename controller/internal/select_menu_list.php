<?php
/**
 * @group 内部接口
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
 *      menu_list: {
 *          ...
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

if ($menu_list = $app->logic_menus->get_all($params['uid'])) {
    foreach ($menu_list as $key => $menu){
        if($menu['lang_key']!='nav-user-avatar') {
            $menu_list[$key]['lang_key'] = $app->lang($menu['lang_key']);
        }
        if (!empty($menu['children'])){
            foreach ($menu['children'] as $key2 => $menu2) {
                if($menu2['lang_key']!='nav-user-avatar') {
                    $menu_list[$key]['children'][$key2]['lang_key'] = $app->lang($menu2['lang_key']);
                }
                if (!empty($menu2['children'])){
                    foreach ($menu2['children'] as $key3 => $menu3) {
                        if($menu3['lang_key']!='nav-user-avatar') {
                            $menu_list[$key]['children'][$key2]['children'][$key3]['lang_key'] = $app->lang($menu3['lang_key']);
                        }
                    }
                }
            }
        }
    }
    unset($params);
    return_json(0, $app->lang('successful_get'),
        [
            'menu_list' => $menu_list,
        ]
    );
}
unset($menu_list, $params);
return_json(2, $app->lang('failure_get'));