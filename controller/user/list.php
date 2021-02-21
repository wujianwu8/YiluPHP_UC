<?php
/**
 * @group 用户
 * @name 用户列表页
 * @desc
 * @method GET
 * @uri /user/list
 * @param integer page 页码 可选 默认为1
 * @param integer page_size 每页条数 可选 默认为10
 * @param string gender 性别 可选 默认为全部,male男性,female女性
 * @param string nickname 昵称 可选 默认为全部
 * @param string identity 登录账号 可选 默认为全部
 * @param string position 位置 可选 默认为全部
 * @param string uid 用户ID 可选 默认为全部
 * @param string birthday_1 起始生日 可选 默认为全部
 * @param string birthday_2 结束生日 可选 默认为全部
 * @param string reg_time_1 起始注册时间 可选 默认为全部
 * @param string reg_time_2 结束注册时间 可选 默认为全部
 * @param string last_active_1 起始活跃时间 可选 默认为全部
 * @param string last_active_2 结束活跃时间 可选 默认为全部
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:view_user_list')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

$page = input::I()->get_int('page',1);
$page_size = input::I()->get_int('page_size',10);
$page_size>500 && $page_size = 500;
$page_size<1 && $page_size = 1;

$where = [];
$gender = input::I()->get_trim('gender',null);
if($gender){
    $where['gender'] = $gender;
}
$nickname = input::I()->get_trim('nickname',null);
if($nickname){
    $where['nickname'] = $nickname;
}
$position = input::I()->get_trim('position',null);
if($position){
    $where['position'] = $position;
}
$uid = input::I()->get_trim('uid',null);
if($uid){
    $where['uid'] = $uid;
}

$birthday_1 = input::I()->get_trim('birthday_1',null);
if($birthday_1){
    $where['birthday_1'] = $birthday_1;
}
$birthday_2 = input::I()->get_trim('birthday_2',null);
if($birthday_2){
    $where['birthday_2'] = $birthday_2;
}

$reg_time_1 = input::I()->get_trim('reg_time_1',null);
if($reg_time_1){
    $where['reg_time_1'] = strtotime($reg_time_1);
}
$reg_time_2 = input::I()->get_trim('reg_time_2',null);
if($reg_time_2){
    $where['reg_time_2'] = strtotime($reg_time_2.' 23:59:59');
}

$last_active_1 = input::I()->get_trim('last_active_1',null);
if($last_active_1){
    $where['last_active_1'] = strtotime($last_active_1);
}
$last_active_2 = input::I()->get_trim('last_active_2',null);
if($last_active_2){
    $where['last_active_2'] = strtotime($last_active_2);
}

$identity = input::I()->get_trim('identity',null);
if($identity){
    $where['identity'] = $identity;
}

$status = input::I()->get_trim('status',null);
if($status!==null){
    $where['status'] = $status;
}

$user_list = logic_user::I()->paging_select_search_user($where, $page, $page_size);

return result('user/list', [
    'user_list' => $user_list,
    'data_count' => model_user_identity::I()->get_user_count(),
    'page' => $page,
    'page_size' => $page_size,
]);