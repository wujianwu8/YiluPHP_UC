<?php
/**
 * @group 用户反馈
 * @name 用户反馈列表页
 * @desc
 * @method GET
 * @uri /user/complaint
 * @param integer page 页码 可选 默认为1
 * @param integer page_size 每页条数 可选 默认为10
 * @param string keyword 关键字 可选 搜索用的关键字
 * @param string status 投诉状态 可选 默认为全部，0新反馈、2正在处理、1已处理
 * @param string user 反馈人 可选 反馈人的昵称或用户ID
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:view_feedback')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

$params = input::I()->validate(
    [
        'status' => 'integer|min:0|max:2|return',
        'keyword' => 'trim|string|return',
        'user' => 'trim|string|return',
    ]);

$page = input::I()->get_int('page',1);
$page_size = input::I()->get_int('page_size',10);
$page_size>500 && $page_size = 500;
$page_size<1 && $page_size = 1;

$where = [];
if (isset($params['status']) && $params['status']!==null){
    $where['status'] = $params['status'];
}
if (isset($params['keyword']) && $params['keyword']!=='' && $params['keyword']!==null){
    $where['keyword'] = $params['keyword'];
}

if (isset($params['user']) && $params['user']!=='' && $params['user']!==null){
    //根据昵称或用户ID搜索投诉人用户
    if($users = model_user::I()->select_user_by_uid_or_nickname($params['user'], 'uid')){
        $where['uids'] = array_column($users, 'uid');
    }
    else{
        unset($users, $params, $where);
        return result('feedback/list', [
            'data_list' => [],
            'data_count' => 0,
        ]);
    }
    unset($users);
}

$res = model_user_feedback::I()->paging_select_user_feedback($where, $page, $page_size);
if ($res['count']>0) {
    $uids = array_column($res['data'], 'uid');
    $user_info = logic_user::I()->select_user_info_by_multi_uids($uids, 'uid,nickname,avatar');
    foreach ($res['data'] as $key => $item){
        if (isset($user_info[$item['uid']])) {
            $res['data'][$key]['nickname'] = $user_info[$item['uid']]['nickname'];
            $res['data'][$key]['avatar'] = $user_info[$item['uid']]['avatar'];
        }
        else{
            $res['data'][$key]['nickname'] = '';
            $res['data'][$key]['avatar'] = $config['default_avatar'];
        }
    }
    unset($uids);
}

unset($user_info, $params);
return result('feedback/list', [
    'data_list' => $res['data'],
    'data_count' => $res['count'],
]);