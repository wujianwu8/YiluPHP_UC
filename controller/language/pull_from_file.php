<?php
/**
 * @group 语言包
 * @name 从PHP文件中拉取语言包内容
 * @desc 追加覆盖的方式
 * @method POST
 * @uri /language/pull_from_file
 * @param integer project_id 项目ID 必选 项目ID
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "从PHP文件中拉取语言内容成功"
 * }
 * @exception
 *  0 从PHP文件中拉取语言内容成功
 *  1 从PHP文件中拉取语言内容失败
 *  2 项目ID参数有误
 *  3 项目不存在
 *  4 PHP语言包目录设置不正确
 *  5 PHP语言包目录不存在
 *  6 保存语言内容入库时出错
 */

if (!$app->logic_permission->check_permission('user_center:pull_lang_from_php_file')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'project_id' => 'required|integer|min:1|return',
    ],
    [
        'project_id.*' => '项目ID参数有误',
    ],
    [
        'project_id.*' => 2,
    ]);


if (!$project_info =$app->model_language_project->find_table(['id' => $params['project_id']])){
    unset($params,$project_info);
    return_code(3,'项目不存在');
}
if (empty($project_info['file_dir'])){
    unset($params,$project_info);
    return_code(4,'PHP语言包目录设置不正确');
}
//读取语言包文件
if (!is_dir($project_info['file_dir'])){
    unset($params,$project_info);
    return_code(5,'PHP语言包目录不存在');
}
$project_info['language_types'] = explode(',', $project_info['language_types']);
$file_list = get_dir_and_file($project_info['file_dir'], 'file');
if (substr($project_info['file_dir'], -1)!='/' && substr($project_info['file_dir'], -1)!='\\'){
    $separator = DIRECTORY_SEPARATOR;
}
else{
    $separator = '';
}
$project_info['file_dir'] .= $separator;

foreach ($file_list as $file){
    $file_info = pathinfo($file);
    if (in_array($file_info['filename'], $project_info['language_types'])){
        $lang_arr = require_once($project_info['file_dir'].$file);
        foreach ($lang_arr as $lang_key => $lang_value){
            $output_type = '-PHP-';
            if ($check = $app->model_language_value->find_table([
                'project_key' => $project_info['project_key'],
                'language_type' => $file_info['filename'],
                'language_key' => $lang_key,
            ], 'output_type')){
                $output_type = explode('-', $check['output_type']);
                $output_type = array_filter($output_type);
                if (!in_array('PHP', $output_type)){
                    $output_type[] = 'PHP';
                }
                $output_type = '-'.implode('-', $output_type).'-';
            }
            $data = [
                'project_key' => $project_info['project_key'],
                'language_type' => $file_info['filename'],
                'language_key' => $lang_key,
                'language_value' => $lang_value,
                'output_type' => $output_type,
                'ctime' => time(),
            ];
            //保存入库
            if(false === $app->model_language_value->insert_language_value($data)){
                unset($params, $project_info, $data, $file_info, $file, $lang_arr, $lang_key, $lang_value);
                return_code(6, '保存语言内容入库时出错');
            }
        }
        unset($lang_arr);
    }
    unset($file_info);
}

unset($params,$where);
//返回结果
return_json(CODE_SUCCESS,'从PHP文件中拉取语言内容成功');
