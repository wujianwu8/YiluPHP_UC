<?php
/**
 * @group 语言包
 * @name 删除语言包项目
 * @desc
 * @method POST
 * @uri /language/delete_project
 * @param integer id 项目ID 必选 项目ID
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 删除成功
 *  1 删除失败
 *  2 项目ID参数有误
 *  3 项目不存在
 *  4 删除失败
 */

if (!logic_permission::I()->check_permission('user_center:delete_lang_project')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'id' => 'required|integer|min:1|return',
    ],
    [
        'id.*' => '项目ID参数有误',
    ],
    [
        'id.*' => 2,
    ]);


if (!$project_info = model_language_project::I()->find_table(['id' => $params['id']], 'project_key')){
    unset($params);
    return code(3,'项目不存在');
}

if(false === model_language_value::I()->destroy(['project_key' => $project_info['project_key']])){
    unset($params,$matches,$where,$project_info);
    return code(4, '删除失败');
}
if(false === model_language_project::I()->delete(['id' => $params['id']])){
    unset($params,$matches,$where);
    return code(1, '删除失败');
}

unset($params);
//返回结果
return json(CODE_SUCCESS,'删除成功');
