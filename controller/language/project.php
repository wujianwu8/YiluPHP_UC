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

if (!logic_permission::I()->check_permission('user_center:view_lang_project_list')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$page = input::I()->get_int('page',1);
$page_size = input::I()->get_int('page_size',30);
$page_size>500 && $page_size = 500;
$page_size<1 && $page_size = 1;

$data_list = model_language_project::I()->paging_select([], $page, $page_size, 'id DESC');

return result('language/project', [
    'data_count' => model_language_project::I()->count([]),
]);