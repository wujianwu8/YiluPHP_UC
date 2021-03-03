<?php
/**
 * @group 语言包
 * @name 保存修改后的语言内容
 * @desc
 * @method POST
 * @uri /language/save_edit_lang_value
 * @param integer project_id 项目ID 必选 项目ID
 * @param string language_type 语言种类 必选 如：cn、en
 * @param string language_key 语言键名 必选
 * @param string language_value 语言内容 必选
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
 *  3 语言种类参数有误
 *  4 语言键名参数有误
 *  5 语言内容参数有误
 *  6 项目不存在
 *  7 语言种类不支持，请检查项目中的设置
 *  8 语言键名填写有误，语言键名只能包含字母、数字、下划线组成，3-200个字
 */

if (!logic_permission::I()->check_permission('user_center:edit_project_lang')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'project_id' => 'required|integer|min:1|return',
        'language_type' => 'required|trim|string|min:2|max:10|return',
        'language_key' => 'required|trim|string|min:2|max:200|return',
        'language_value' => 'required|trim|string|return',
    ],
    [
        'project_id.*' => '项目ID参数有误',
        'language_type.*' => '语言种类参数有误',
        'language_key.*' => '语言键名参数有误',
        'language_value.*' => '语言内容参数有误',
    ],
    [
        'project_id.*' => 2,
        'language_type.*' => 3,
        'language_key.*' => 4,
        'language_value.*' => 5,
    ]);

if (preg_match('/^[a-zA-Z0-9_]{3,200}$/', $params['language_key'], $matches)==false){
    unset($params, $matches);
    return code(8,'语言键名填写有误，语言键名只能包含字母、数字、下划线组成，3-200个字');
}
unset($matches);

if (!$project_info = model_language_project::I()->find_table(['id' => $params['project_id']])){
    unset($params, $project_info);
    return code(6,'项目不存在');
}
$project_info['language_types'] = explode(',', $project_info['language_types']);
if (!in_array($params['language_type'], $project_info['language_types'])){
    unset($params, $project_info);
    return code(7,'语言种类不支持，请检查项目中的设置');
}

$data = [
    'project_key' => $project_info['project_key'],
    'language_type' => $params['language_type'],
    'language_key' => $params['language_key'],
    'language_value' => $params['language_value'],
    'ctime' => time(),
];
//保存入库
if(false === model_language_value::I()->insert_language_value($data)){
    unset($params, $data, $project_info);
    return code(1, '保存失败');
}

unset($params, $data, $project_info);
//返回结果
return json(0,YiluPHP::I()->lang('save_successfully'));
