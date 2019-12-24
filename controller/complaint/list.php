<?php
/**
 * @name 用户投诉列表页
 * @desc
 * @method GET
 * @uri /user/complaint
 * @param integer page 页码 可选 默认为1
 * @param integer page_size 每页条数 可选 默认为10
 * @param string keyword 关键字 可选 搜索用的关键字
 * @param string status 投诉状态 可选 默认为全部，0新投诉、2正在处理、1已处理
 * @param string complaint_user 投诉人 可选 投诉人的昵称或用户ID
 * @param string respondent_user 被投诉人 可选 被投诉人的昵称或用户ID
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:view_complaint_user_list')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'status' => 'integer|min:0|max:2|return',
        'keyword' => 'trim|string|return',
        'complaint_user' => 'trim|string|return',
        'respondent_user' => 'trim|string|return',
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

if (isset($params['complaint_user']) && $params['complaint_user']!=='' && $params['complaint_user']!==null){
    //根据昵称或用户ID搜索投诉人用户
    if($users = $app->model_user->select_user_by_uid_or_nickname($params['complaint_user'], 'uid')){
        $where['complaint_uids'] = array_column($users, 'uid');
    }
    else{
        unset($users, $params, $where);
        return_result('complaint/list', [
            'data_list' => [],
            'data_count' => 0,
            'page' => $page,
            'page_size' => $page_size,
        ]);
    }
    unset($users);
}
if (isset($params['respondent_user']) && $params['respondent_user']!=='' && $params['respondent_user']!==null){
    //根据昵称或用户ID搜索投诉人用户
    if($users = $app->model_user->select_user_by_uid_or_nickname($params['respondent_user'], 'uid')){
        $where['respondent_uids'] = array_column($users, 'uid');
    }
    else{
        unset($users, $params, $where);
        return_result('complaint/list', [
            'data_list' => [],
            'data_count' => 0,
            'page' => $page,
            'page_size' => $page_size,
        ]);
    }
    unset($users);
}

$res = $app->model_user_complaint->paging_select_user_complaint($where, $page, $page_size);
if ($res['count']>0) {
    $uids = array_merge(array_column($res['data'], 'respondent_uid'), array_column($res['data'], 'complaint_uid'));
    $user_info = $app->logic_user->select_user_info_by_multi_uids($uids, 'uid,nickname,avatar');
    foreach ($res['data'] as $key => $item){
        if (isset($user_info[$item['respondent_uid']])) {
            $res['data'][$key]['respondent_nickname'] = $user_info[$item['respondent_uid']]['nickname'];
            $res['data'][$key]['respondent_avatar'] = $user_info[$item['respondent_uid']]['avatar'];
        }
        else{
            $res['data'][$key]['respondent_nickname'] = '';
            $res['data'][$key]['respondent_avatar'] = $config['default_avatar'];
        }
        if (isset($user_info[$item['complaint_uid']])) {
            $res['data'][$key]['complaint_nickname'] = $user_info[$item['complaint_uid']]['nickname'];
            $res['data'][$key]['complaint_avatar'] = $user_info[$item['complaint_uid']]['avatar'];
        }
        else{
            $res['data'][$key]['complaint_nickname'] = '';
            $res['data'][$key]['complaint_avatar'] = $config['default_avatar'];
        }
    }
    unset($uids);
}

unset($user_info, $params);
return_result('complaint/list', [
    'data_list' => $res['data'],
    'data_count' => $res['count'],
    'page' => $page,
    'page_size' => $page_size,
]);