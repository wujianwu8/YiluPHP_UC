<?php
/**
 * @group 语言包
 * @name 保存语言可输出的文件类型
 * @desc
 * @method POST
 * @uri /language/save_lang_output_type
 * @param integer project_id 项目ID 必选 项目ID
 * @param string language_key 语言键名 必选
 * @param string output_type 可输出类型 必选 可选值有：PHP、JS，多个值之间使用英文半角逗号分隔
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
 *  3 语言键名参数有误
 *  4 可输出类型参数有误
 *  5 语言键名填写有误，语言键名只能包含字母、数字、下划线组成，3-200个字
 *  6 可输出类型参数有误
 *  7 项目不存在
 */

if (!logic_permission::I()->check_permission('user_center:edit_lang_project')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'project_id' => 'required|integer|min:1|return',
        'language_key' => 'required|trim|string|min:2|max:200|return',
        'output_type' => 'required|trim|string|max:20|return',
    ],
    [
        'project_id.*' => '项目ID参数有误',
        'language_key.*' => '语言键名参数有误',
        'output_type.*' => '可输出类型参数有误',
    ],
    [
        'project_id.*' => 2,
        'language_key.*' => 3,
        'output_type.*' => 4,
    ]);

if (preg_match('/^[a-zA-Z0-9_]{3,200}$/', $params['language_key'], $matches)==false){
    unset($params, $matches);
    return code(5,'语言键名填写有误，语言键名只能包含字母、数字、下划线组成，3-200个字');
}
unset($matches);

$output_type = explode(',', $params['output_type']);
$params['output_type'] = [];
foreach ($output_type as $value){
    if (in_array($value, ['PHP', 'JS'])){
        $params['output_type'][] = $value;
    }
    else if (trim($value)!=''){
        unset($params, $output_type);
        return code(6,'可输出类型参数有误');
    }
}
if ($params['output_type']){
    $params['output_type'] = '-'.implode('-', $params['output_type']).'-';
}
else{
    $params['output_type'] = '';
}

if (!$project_info = model_language_project::I()->find_table(['id' => $params['project_id']])){
    unset($params, $project_info);
    return code(7,'项目不存在');
}
$project_info['language_types'] = explode(',', $project_info['language_types']);

foreach ($project_info['language_types'] as $language_type) {
    $data = [
        'project_key' => $project_info['project_key'],
        'language_type' => $language_type,
        'language_key' => $params['language_key'],
        'output_type' => $params['output_type'],
        'ctime' => time(),
    ];
    //保存入库
    if (false === model_language_value::I()->insert_language_value($data)) {
        unset($params, $data, $project_info);
        return code(1, '保存失败');
    }
}

unset($params, $data, $project_info);
//返回结果
return json(0,YiluPHP::I()->lang('save_successfully'));
