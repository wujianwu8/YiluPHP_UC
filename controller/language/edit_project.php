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

if (!logic_permission::I()->check_permission('user_center:edit_lang_project')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'project_id' => 'required|integer|min:1|return',
    ],
    [
        'project_id.*' => '项目ID参数错误',
    ],
    [
        'project_id.*' => 1,
    ]);

if (!$project_info=model_language_project::I()->find_table(['id'=>$params['project_id']])){
    return code(2, '项目不存在');
}

return result('language/edit_project');