<?php
/**
 * @group 角色
 * @name 角色列表页
 * @desc
 * @method GET
 * @uri /role/list
 * @param integer page 页码 可选 默认为1
 * @param integer page_size 每页条数 可选 默认为10
 * @param string role_name 应用名称 可选 默认为全部
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:view_role_list')) {
    return_code(100, $app->lang('not_authorized'));
}

$page = $app->input->get_int('page',1);
$page_size = $app->input->get_int('page_size',10);
$page_size>500 && $page_size = 500;
$page_size<1 && $page_size = 1;

$where = [];
$role_name = $app->input->get_trim('role_name',null);
if($role_name){
    $where['role_name'] = [
        'symbol' => 'LIKE',
        'value' => '%'.$app_name.'%',
    ];
}

$data_list = $app->model_role->paging_select($where, $page, $page_size, 'id DESC');
return_result('role/list', [
    'data_list' => $data_list,
    'data_count' => $app->model_role->count($where),
    'page' => $page,
    'page_size' => $page_size,
]);