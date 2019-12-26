<?php
/**
 * @group 语言包
 * @name 语言包的项目列表页
 * @desc
 * @method GET
 * @uri /language/project
 * @param integer page 页码 可选 默认为1
 * @param integer page_size 每页条数 可选 默认为20
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:view_lang_project_list')) {
    return_code(100, $app->lang('not_authorized'));
}

$page = $app->input->get_int('page',1);
$page_size = $app->input->get_int('page_size',30);
$page_size>500 && $page_size = 500;
$page_size<1 && $page_size = 1;

$data_list = $app->model_language_project->paging_select([], $page, $page_size, 'id DESC');
return_result('language/project', [
    'data_list' => $data_list,
    'data_count' => $app->model_language_project->count([]),
    'page' => $page,
    'page_size' => $page_size,
]);