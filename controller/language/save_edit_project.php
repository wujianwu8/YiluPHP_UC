<?php
/**
 * @group 语言包
 * @name 保存修改后的语言包项目
 * @desc
 * @method POST
 * @uri /language/save_edit_project
 * @param integer id 项目ID 必选 项目ID
 * @param string project_name 项目名 必选 可以是语言键名
 * @param string file_dir PHP语言包目录 必选
 * @param string js_file_dir JS语言包目录 必选
 * @param string language_types 语言种类 必选 语言种类只能使用字母、数字、下划线、中横线，半角逗号，长度在2-200个字，多个语种使用半角逗号分隔，如：zh,en
 * @param string description 描述 可选
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 保存成功
 *  1 保存失败
 *  2 项目ID参数有误
 *  3 项目名参数有误
 *  4 语言包目录参数有误
 *  5 语言各类参数有误
 *  6 描述太长了
 *  7 语言种类设置错误，语言种类只能使用字母、数字、下划线、中横线，半角逗号，长度在2-200个字，多个语种使用半角逗号分隔，如：zh,en
 *  8 项目不存在
 *  9 JS语言包目录参数有误
 */

if (!logic_permission::I()->check_permission('user_center:edit_lang_project')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

$params = input::I()->validate(
    [
        'id' => 'required|integer|min:1|return',
        'project_name' => 'required|trim|string|min:2|max:40|return',
        'file_dir' => 'required|trim|string|min:2|max:200|return',
        'js_file_dir' => 'required|trim|string|min:2|max:200|return',
        'language_types' => 'required|trim|string|min:2|max:200|return',
        'description' => 'trim|string|max:200|return',
    ],
    [
        'id.*' => '项目ID参数有误',
        'project_name.*' => '项目名参数有误',
        'file_dir.*' => 'PHP语言包目录参数有误',
        'js_file_dir.*' => 'JS语言包目录参数有误',
        'language_types.*' => '语言各类参数有误',
        'description.*' => '描述太长了',
    ],
    [
        'id.*' => 2,
        'project_name.*' => 3,
        'file_dir.*' => 4,
        'js_file_dir.*' => 9,
        'language_types.*' => 5,
        'description.*' => 6,
    ]);


if (preg_match('/^[a-zA-Z0-9\-,_]{2,200}$/', $params['language_types'], $matches)==false){
    unset($params,$matches);
    return code(7,'语言种类设置错误，语言种类只能使用字母、数字、下划线、中横线，半角逗号，长度在2-200个字，多个语种使用半角逗号分隔，如：zh,en');
}

if (!model_language_project::I()->find_table(['id' => $params['id']])){
    unset($params,$matches);
    return code(9,'项目不存在');
}

$where = ['id' => $params['id']];
unset($params['id']);
//保存入库
if(false === model_language_project::I()->update_table($where, $params)){
    unset($params,$matches,$where);
    return code(1, '保存失败');
}

unset($params,$matches,$where);
//返回结果
return json(0,YiluPHP::I()->lang('save_successfully'));
