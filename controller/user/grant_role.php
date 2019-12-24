<?php
/**
 * @name 给用户分配角色页
 * @desc
 * @method POST
 * @uri /user/grant_role/{uid}
 * @param integer uid 用户ID 必选
 * @return html
 */

if (!$app->logic_permission->check_permission('user_center:view_user_role')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'uid' => 'required|integer|min:1|return',
    ],
    [
        'uid.*' => '用户ID有误',
    ],
    [
        'uid.*' => 2,
    ]);

if(!$user_info = $app->model_user->find_table(['uid' => $params['uid']], 'uid,nickname', $params['uid'])){
    unset($params);
    return_code(1, '用户不存在');
}

//获取用户已有的角色ID
if($having_role_ids = $app->model_user_role->select_all(['uid'=>$params['uid']], '', 'role_id')){
    $having_role_ids = array_column($having_role_ids, 'role_id');
}

if($role_list = $app->model_role->select_all([])) {
    $in_arr = $not_in_arr = [];
    foreach ($role_list as $key => $item) {
        $item['role_name_lang'] = $app->lang($item['role_name']);
        if (in_array($item['id'], $having_role_ids)) {
            $item['is_own'] = true;
            $in_arr[] = $item;
        } else {
            $item['is_own'] = false;
            $not_in_arr[] = $item;
        }
    }
    $role_list = array_merge($in_arr, $not_in_arr);
    unset($in_arr, $not_in_arr);
}
unset($params, $having_role_ids);

return_result('role/grant_role', [
    'role_list' => $role_list,
    'user_info' => $user_info,
]);