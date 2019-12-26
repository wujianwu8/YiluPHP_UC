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

if (!$app->logic_permission->check_permission('user_center:view_feedback')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'status' => 'integer|min:0|max:2|return',
        'keyword' => 'trim|string|return',
        'user' => 'trim|string|return',
    ]);

$page = $app->input->get_int('page',1);
$page_size = $app->input->get_int('page_size',10);
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
    if($users = $app->model_user->select_user_by_uid_or_nickname($params['user'], 'uid')){
        $where['uids'] = array_column($users, 'uid');
    }
    else{
        unset($users, $params, $where);
        return_result('feedback/list', [
            'data_list' => [],
            'data_count' => 0,
            'page' => $page,
            'page_size' => $page_size,
        ]);
    }
    unset($users);
}

$res = $app->model_user_feedback->paging_select_user_feedback($where, $page, $page_size);
if ($res['count']>0) {
    $uids = array_column($res['data'], 'uid');
    $user_info = $app->logic_user->select_user_info_by_multi_uids($uids, 'uid,nickname,avatar');
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
return_result('feedback/list', [
    'data_list' => $res['data'],
    'data_count' => $res['count'],
    'page' => $page,
    'page_size' => $page_size,
]);