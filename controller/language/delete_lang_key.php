<?php
/**
 * @group 语言包
 * @name 保存修改后的语言内容
 * @desc
 * @method POST
 * @uri /language/delete_lang_key
 * @param integer project_id 项目ID 必选 项目ID
 * @param string language_key 语言键名 必选
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "删除成功"
 * }
 * @exception
 *  0 删除成功
 *  1 删除失败
 *  2 项目ID参数有误
 *  3 语言种类参数有误
 *  4 语言键名参数有误
 *  5 语言内容参数有误
 *  6 项目不存在
 *  7 语言种类不支持，请检查项目中的设置
 */

if (!$app->logic_permission->check_permission('user_center:delete_project_lang_key')) {
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
    return_code(8,'语言键名参数有误');
}
unset($matches);

if (!$project_info = $app->model_language_project->find_table(['id' => $params['project_id']])){
    unset($params, $project_info);
    return_code(6,'项目不存在');
}

//保存入库
if(false === $app->model_language_value->destroy([
        'project_key' => $project_info['project_key'],
        'language_key' => $params['language_key'],
    ])){
    unset($params, $project_info);
    return_code(1, '删除失败');
}

unset($params, $project_info);
//返回结果
return_json(CODE_SUCCESS,'删除成功');
