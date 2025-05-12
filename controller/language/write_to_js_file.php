<?php
/**
 * @group 语言包
 * @name 把语言包内容写入JS文件
 * @desc 完全替换覆盖的方式
 * @method POST
 * @uri /language/write_to_js_file
 * @param integer project_id 项目ID 必选 项目ID
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "把语言包内容写入JS文件成功"
 * }
 * @exception
 *  0 把语言包内容写入JS文件成功
 *  1 把语言包内容写入JS文件失败
 *  2 项目ID参数有误
 *  3 项目不存在
 *  4 JS语言包目录设置不正确
 *  5 JS语言包目录不存在
 *  6
 *  7
 *  8
 */

if (!logic_permission::I()->check_permission('user_center:write_lang_to_js_file')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'project_id' => 'required|integer|min:1|return',
    ],
    [
        'project_id.*' => '项目ID参数有误',
    ],
    [
        'project_id.*' => 2,
    ]);


if (!$project_info = model_language_project::I()->find_table(['id' => $params['project_id']])) {
    unset($params, $project_info);
    return code(3, '项目不存在');
}

logic_language::I()->write_to_js_file($project_info);

unset($params, $where, $project_info);
//返回结果
return json(0, '把语言包内容写入JS文件成功');
