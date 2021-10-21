<?php
/**
 * @group 内部接口
 * @name 具有某权限的所有用户
 * @desc 分页获取具有某权限的所有用户
 * @method GET|POST
 * @uri /internal/select_permission_user
 * @param string sign 签名 必选 内部接口公共参数
 * @param integer time 请求时间 必选 内部接口公共参数，发起请求的时间戳，精确到秒
 * @param string app_id 应用ID 必选 内部接口公共参数，分配给发起方的应用ID
 * @param string dtype 返回数据格式 可选 内部接口公共参数，可选项有：json、jsonp、html
 * @param string lang 语言类型 可选 内部接口公共参数，返回的数据的语言类型，cn简体中文，en为英文，默认为cn
 * @param string permission_key 权限键 必选 不包含app_id
 * @param integer page 页码 可选 默认为1
 * @param integer page_size 每页数量 可选 默认为20
 * @return JSON
 * {
 *  code: 0, //0获取成功
 *  msg: "获取成功",
 *  data: {
 *      count: 15 //用户总数
 *      user_list: [ //用户列表
 *          {}
 *      ]
 *  }
 * }
 * @exception
 *   0 获取成功
 *   1 permission_key参数错误
 */

$params = input::I()->validate(
    [
        'permission_key' => 'required|trim|string|min:1|max:256|return',
        'app_id' => 'required|string|min:1|return',
        'page' => 'integer|min:1|return',
        'page_size' => 'integer|min:1|return',
    ],
    [],
    [
        'permission_key.*' => 1,
    ]);

$page = empty($params['page'])?1:$params['page'];
$page_size = empty($params['page_size'])?20:$params['page_size'];
if(!$permission_info = model_permission::I()->find_table(['app_id'=>$params['app_id'], 'permission_key'=>$params['permission_key']], 'permission_id')){
    return json(2, YiluPHP::I()->lang('permission_not_found'));
}
$user_list = [];
$count = 0;
if($uids = model_user_permission::I()->paging_select(['permission_id'=>$permission_info['permission_id']], $page, $page_size, 'uid ASC', 'uid')){
    $count = model_user_permission::I()->count(['permission_id'=>$permission_info['permission_id']]);
    $user_list = logic_user::I()->select_user_info_by_multi_uids(array_column($uids, 'uid'), 'uid,nickname,avatar');
}

return json(0, YiluPHP::I()->lang('successful_get'), [
    'count' => $count,
    'user_list' => $user_list,
]);