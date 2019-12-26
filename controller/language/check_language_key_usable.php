<?php
/**
 * @group 语言包
 * @name 检查项目的语言键是否可用
 * @desc 新建语言键时使用
 * @method POST
 * @uri /language/check_language_key_usable
 * @param integer project_id 项目ID 必选 项目ID
 * @param string language_key 语言键名 必选
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 语言键名可用
 *  1 在{project_name}项目中，此语言键名已经存在
 *  2 项目ID参数有误
 *  3 语言键名参数有误
 *  4 项目不存在
 *  5 语言键名填写有误，语言键名只能包含字母、数字、下划线组成，3-200个字
 */

if (!$app->logic_permission->check_permission('user_center:add_project_lang_key')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'project_id' => 'required|integer|min:1|return',
        'language_key' => 'required|trim|string|min:2|max:200|return',
    ],
    [
        'project_id.*' => '项目ID参数有误',
        'language_key.*' => '语言键名参数有误',
    ],
    [
        'project_id.*' => 2,
        'language_key.*' => 3,
    ]);

if (preg_match('/^[a-zA-Z0-9_]{3,200}$/', $params['language_key'], $matches)==false){
    unset($params, $matches);
    return_code(5,'语言键名填写有误，语言键名只能包含字母、数字、下划线组成，3-200个字');
}
unset($matches);

if (!$project_info = $app->model_language_project->find_table(['id' => $params['project_id']])){
    unset($params, $project_info);
    return_code(4,'项目不存在');
}

if ($app->model_language_value->find_table(
    ['project_key' => $project_info['project_key'], 'language_key' => $params['language_key']],
    'id')){
    unset($params);
    return_code(1,'在'.$project_info['project_name'].'项目中，此语言键名已经存在');
}

unset($params, $project_info);
//返回结果
return_json(0,'语言键名可用');
