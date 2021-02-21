<?php
/**
 * @group 语言包
 * @name 从文件中拉取语言包内容
 * @desc 追加覆盖的方式
 * @method POST
 * @uri /language/pull_from_js_file
 * @param integer project_id 项目ID 必选 项目ID
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "从JS文件中拉取语言内容成功"
 * }
 * @exception
 *  0 从JS文件中拉取语言内容成功
 *  1 从JS文件中拉取语言内容失败
 *  2 项目ID参数有误
 *  3 项目不存在
 *  4 JS语言包目录设置不正确
 *  5 JS语言包目录不存在
 *  6 保存语言内容入库时出错
 *  7 解析JS中的数据失败
 */

if (!logic_permission::I()->check_permission('user_center:pull_lang_from_js_file')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
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


if (!$project_info =model_language_project::I()->find_table(['id' => $params['project_id']])){
    unset($params,$project_info);
    return code(3,'项目不存在');
}
if (empty($project_info['js_file_dir'])){
    unset($params,$project_info);
    return code(4,'JS语言包目录设置不正确');
}
//读取语言包文件
if (!is_dir($project_info['js_file_dir'])){
    unset($params,$project_info);
    return code(5,'JS语言包目录不存在');
}
$project_info['language_types'] = explode(',', $project_info['language_types']);
$file_list = get_dir_and_file($project_info['js_file_dir'], 'file');
if (substr($project_info['js_file_dir'], -1)!='/' && substr($project_info['js_file_dir'], -1)!='\\'){
    $separator = DIRECTORY_SEPARATOR;
}
else{
    $separator = '';
}
$project_info['js_file_dir'] .= $separator;

foreach ($file_list as $file){
    $file_info = pathinfo($file);
    if (in_array($file_info['filename'], $project_info['language_types'])){
        $lang_arr = file_get_contents($project_info['js_file_dir'].$file);
        $lang_arr = preg_replace("/[\r\n]+\s*(\w)/", '"$1', $lang_arr);
        $lang_arr = preg_replace("/(\w):/", '$1":', $lang_arr);
        $lang_arr = preg_replace("/[\r\n]+\s/", '', $lang_arr);
        $lang_arr = str_replace('\\\\', '\\\\\\\\', $lang_arr);
        $lang_arr = str_replace('\\\'', '\'', $lang_arr);
        preg_match("/\{.+\}/i", $lang_arr, $matches);
        if ($matches) {
            if ($lang_arr = json_decode($matches[0], true)) {
                foreach ($lang_arr as $lang_key => $lang_value) {
                    $output_type = '-JS-';
                    if ($check = model_language_value::I()->find_table([
                        'project_key' => $project_info['project_key'],
                        'language_type' => $file_info['filename'],
                        'language_key' => $lang_key,
                    ], 'output_type')){
                        $output_type = explode('-', $check['output_type']);
                        $output_type = array_filter($output_type);
                        if (!in_array('JS', $output_type)){
                            $output_type[] = 'JS';
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
                    if (false === model_language_value::I()->insert_language_value($data)) {
                        unset($params, $project_info, $data, $file_info, $file, $lang_arr, $lang_key, $lang_value);
                        return code(6, '保存语言内容入库时出错');
                    }
                }
            } else {
                unset($params, $project_info, $data, $file_info, $file, $lang_arr, $lang_key, $lang_value);
                return code(7, '解析JS中的数据失败');
            }
        }
        unset($lang_arr);
    }
    unset($file_info);
}

unset($params,$where);
//返回结果
return json(CODE_SUCCESS,'从JS文件中拉取语言内容成功');
