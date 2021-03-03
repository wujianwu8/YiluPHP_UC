<?php
/**
 * @group 应用系统
 * @name 应用列表页
 * @desc
 * @method GET
 * @uri /application/list
 * @param integer page 页码 可选 默认为1
 * @param integer page_size 每页条数 可选 默认为10
 * @param string app_id 应用ID 可选 默认为全部
 * @param string app_name 应用名称 可选 默认为全部
 * @param integer status 状态 可选 默认为全部，状态：0不可用，1可用
 * @param integer is_fixed 固定应用 可选 默认为全部，0非固定应用，1为固定应用
 * @param integer user 用户 可选 默认为全部，用户ID或昵称
 * @param string index_url 应用首页 可选 默认为全部，首页地址
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:view_application_list')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$page = input::I()->get_int('page',1);
$page_size = input::I()->get_int('page_size',10);
$page_size>500 && $page_size = 500;
$page_size<1 && $page_size = 1;

$where = [];
$app_id = input::I()->get_trim('app_id',null);
if($app_id){
    $where['app_id'] = $app_id;
}
$app_name = input::I()->get_trim('app_name',null);
if($app_name){
    $where['app_name'] = [
        'symbol' => 'LIKE',
        'value' => '%'.$app_name.'%',
    ];
}
$index_url = input::I()->get_trim('index_url',null);
if($index_url){
    $where['index_url'] = [
        'symbol' => 'LIKE',
        'value' => '%'.$index_url.'%',
    ];
}
$user = input::I()->get_trim('user',null);
if($user){
    $users = model_user::I()->select_user_by_uid_or_nickname($user, 'uid', 100);
    $where['uid'] = [
        'symbol' => 'IN',
        'value' => array_column($users, 'uid'),
    ];;
}

$status = input::I()->get_int('status',null);
if($status!==null){
    $where['status'] = $status;
}
$is_fixed = input::I()->get_int('is_fixed',null);
if($is_fixed!==null){
    $where['is_fixed'] = $is_fixed;
}

$data_list = model_application::I()->paging_select($where, $page, $page_size, 'ctime DESC');
$uids = array_column($data_list, 'uid');
$uids = array_unique($uids);
$user_infos = logic_user::I()->select_user_info_by_multi_uids($uids, 'uid, nickname', 'uid');
foreach ($data_list as $key=>$item){
    if (empty($item['uid'])){
        $data_list[$key]['nickname'] = '系统应用';
    }
    else if (isset($user_infos[$item['uid']])){
        $data_list[$key]['nickname'] = $user_infos[$item['uid']]['nickname'];
    }
    else{
        $data_list[$key]['nickname'] = '';
    }
    $data_list[$key]['app_secret'] = '';
    $tmp = preg_replace('/[\r\n\,]+/',',', $item['app_white_ip']);
    $tmp = explode(',', $tmp);
    $data_list[$key]['app_white_ip'] = '';
    foreach ($tmp as $index => $value){
        $data_list[$key]['app_white_ip'] .= ($index>0?($index%2==0 ? "\r\n":','):'').$value;
    }
}
return result('application/list', [
    'data_list' => $data_list,
    'data_count' => model_application::I()->count($where),
    'page' => $page,
    'page_size' => $page_size,
]);