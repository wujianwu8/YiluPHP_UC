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
        $lang_js = file_get_contents($project_info['js_file_dir'].$file);
        $parse_error = '';
        $lang_arr = parse_js_language_file($lang_js, $parse_error);
        if (!is_array($lang_arr)) {
            write_applog('ERROR', '解析JS语言包失败 file='.$file.', error='.$parse_error);
            unset($params, $project_info, $data, $file_info, $file, $lang_arr, $lang_js, $lang_key, $lang_value);
            return code(7, '解析JS中的数据失败：'.$parse_error);
        }
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
                unset($params, $project_info, $data, $file_info, $file, $lang_arr, $lang_js, $lang_key, $lang_value);
                return code(6, '保存语言内容入库时出错');
            }
        }
        unset($lang_arr, $lang_js, $parse_error);
    }
    unset($file_info);
}

unset($params,$where);
//返回结果
return json(CODE_SUCCESS,'从JS文件中拉取语言内容成功');

/**
 * 从 `var language = {...};` 中读取语言对象。
 * 按字符串状态寻找对象边界，避免文案中的 {$name} 等花括号干扰；
 * 同时移除字符串内部意外出现的真实换行，兼容不同系统的换行格式。
 */
function parse_js_language_file($source, &$error='')
{
    $error = '';
    if (!is_string($source) || $source === '') {
        $error = '文件为空或读取失败';
        return null;
    }
    $source = preg_replace('/^\xEF\xBB\xBF/', '', $source);
    if (!preg_match('/\b(?:var|let|const)\s+language\s*=\s*/u', $source, $declaration, PREG_OFFSET_CAPTURE)) {
        $error = '找不到language变量声明';
        return null;
    }

    $search_offset = $declaration[0][1] + strlen($declaration[0][0]);
    $start = strpos($source, '{', $search_offset);
    if ($start === false) {
        $error = '找不到语言对象开始位置';
        return null;
    }

    $length = strlen($source);
    $depth = 0;
    $in_string = false;
    $escaped = false;
    $json = '';
    $end_found = false;
    for ($index = $start; $index < $length; $index++) {
        $char = $source[$index];
        if ($in_string) {
            //JS双引号字符串中不允许直接换行；线上文件若意外换行，按原逻辑拼接为同一行。
            if ($char === "\r" || $char === "\n") {
                if ($char === "\r" && $index + 1 < $length && $source[$index + 1] === "\n") {
                    $index++;
                }
                continue;
            }
            //JS允许在双引号字符串中写 \'，JSON不接受这种转义。
            if (!$escaped && $char === '\\' && $index + 1 < $length && $source[$index + 1] === "'") {
                continue;
            }
            $json .= $char;
            if ($escaped) {
                $escaped = false;
            }
            else if ($char === '\\') {
                $escaped = true;
            }
            else if ($char === '"') {
                $in_string = false;
            }
            continue;
        }

        $json .= $char;
        if ($char === '"') {
            $in_string = true;
        }
        else if ($char === '{') {
            $depth++;
        }
        else if ($char === '}') {
            $depth--;
            if ($depth === 0) {
                $end_found = true;
                break;
            }
        }
    }

    if (!$end_found || $in_string || $depth !== 0) {
        $error = '语言对象结构不完整';
        return null;
    }

    //语言文件的键名是标准JS标识符，为其补上JSON要求的双引号。
    $json = preg_replace('/^(\s*)([A-Za-z_][A-Za-z0-9_]*)\s*:/m', '$1"$2":', $json);
    $result = json_decode($json, true);
    if (!is_array($result)) {
        $error = json_last_error_msg();
        return null;
    }
    return $result;
}
