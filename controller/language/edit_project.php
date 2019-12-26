<?php
/**
 * @group 语言包
 * @name 编辑语言包项目信息页
 * @desc
 * @method GET
 * @uri /language/edit_project/{project_id}
 * @param integer project_id 项目ID 必选 嵌入在URL中
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:edit_lang_project')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'project_id' => 'required|integer|min:1|return',
    ],
    [
        'project_id.*' => '项目ID参数错误',
    ],
    [
        'project_id.*' => 1,
    ]);

if (!$project_info=$app->model_language_project->find_table(['id'=>$params['project_id']])){
    return_code(2, '项目不存在');
}

return_result('language/edit_project',[
    'project_info' => $project_info
]);