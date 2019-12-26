<?php
/**
 * @group 语言包
 * @name 保存新的语言包项目
 * @desc
 * @method POST
 * @uri /language/save_add_project
 * @param string project_key 项目键名 必选 项目键名，仅由字母、数字、下划线组成
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
 *  2 项目键名参数有误
 *  3 项目名参数有误
 *  4 PHP语言包目录参数有误
 *  5 语言各类参数有误
 *  6 描述太长了
 *  7 项目键名只能使用字母、数字、下划线，长度在3-30个字
 *  8 语言种类设置错误，语言种类只能使用字母、数字、下划线、中横线，半角逗号，长度在2-200个字，多个语种使用半角逗号分隔，如：zh,en
 *  9 项目键名已经存在，换一个吧
 * 10 JS语言包目录参数有误
 */

if (!$app->logic_permission->check_permission('user_center:add_lang_project')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'project_key' => 'required|trim|string|min:3|max:30|return',
        'project_name' => 'required|trim|string|min:2|max:40|return',
        'file_dir' => 'required|trim|string|min:2|max:200|return',
        'js_file_dir' => 'required|trim|string|min:2|max:200|return',
        'language_types' => 'required|trim|string|min:2|max:200|return',
        'description' => 'trim|string|max:200|return',
    ],
    [
        'project_key.*' => '项目键名参数有误',
        'project_name.*' => '项目名参数有误',
        'file_dir.*' => 'PHP语言包目录参数有误',
        'js_file_dir.*' => 'JS语言包目录参数有误',
        'language_types.*' => '语言各类参数有误',
        'description.*' => '描述太长了',
    ],
    [
        'project_key.*' => 2,
        'project_name.*' => 3,
        'file_dir.*' => 4,
        'js_file_dir.*' => 10,
        'language_types.*' => 5,
        'description.*' => 6,
    ]);

if (preg_match('/^[a-zA-Z0-9_]{3,30}$/', $params['project_key'], $matches)==false){
    unset($params,$matches);
    return_code(7,'项目键名只能使用字母、数字、下划线，长度在3-30个字');
}

if (preg_match('/^[a-zA-Z0-9\-,_]{2,200}$/', $params['language_types'], $matches)==false){
    unset($params,$matches);
    return_code(8,'语言种类设置错误，语言种类只能使用字母、数字、下划线、中横线，半角逗号，长度在2-200个字，多个语种使用半角逗号分隔，如：zh,en');
}

if ($app->model_language_project->find_table(['project_key' => $params['project_key']])){
    unset($params,$matches);
    return_code(9,'项目键名已经存在，换一个吧');
}

//保存入库
if(false === $role_id=$app->model_language_project->insert_table($params)){
    unset($params,$matches);
    return_code(1, '保存失败');
}

unset($params,$matches);
//返回结果
return_json(CODE_SUCCESS,'保存成功');
