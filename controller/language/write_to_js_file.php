<?php
/**
 * @group 语言包
 * @name 把语言包内容写入JS文件
 * @desc 完全替换覆盖的方式
 * @method POST
 * @uri /language/write_to_js_file
 * @param integer project_id 项目ID 必选 项目ID
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "把语言包内容写入JS文件成功"
 * }
 * @exception
 *  0 把语言包内容写入JS文件成功
 *  1 把语言包内容写入JS文件失败
 *  2 项目ID参数有误
 *  3 项目不存在
 *  4 JS语言包目录设置不正确
 *  5 JS语言包目录不存在
 *  6
 *  7
 *  8
 */

if (!logic_permission::I()->check_permission('user_center:write_lang_to_js_file')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
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
unset($separator);

$if_have_js_file = false;
foreach ($file_list as $file) {
    $file_info = pathinfo($file);
    if (strtolower($file_info['extension']) == 'js') {
        $if_have_js_file = true;
        break;
    }
}
if ($if_have_js_file) {
    //备份原来的语言包文件
    $zip = new ZipArchive();
    $zip->open($project_info['js_file_dir'] . 'bak-' . date('Ymd-His') . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
    foreach ($file_list as $file) {
        $file_info = pathinfo($file);
        if (strtolower($file_info['extension']) != 'js') {
            continue;
        }
        $zip->addFile($project_info['js_file_dir'] . $file, $file);
    }
    $zip->close();
    unset($zip);
}

foreach ($project_info['language_types'] as $lang){
    if(!$langfile = fopen($project_info['js_file_dir'].$lang.'.js', "w")){
        return code(10,'打开文件失败：'.$project_info['js_file_dir'].$lang.'.js');
    }
    $txt = "var language = {\r\n";
    fwrite($langfile, $txt);
    //读取该项目、该语言的所有语言键及内容，按语言键字母升序排序
    if($lang_list = model_language_value::I()->select_all([
        'project_key' => $project_info['project_key'],
        'language_type' => $lang,
        'output_type' => [
            'symbol' => 'LIKE',
            'value' => '%-JS-%'
        ]
    ], 'language_key ASC','language_key,language_value')){
        foreach ($lang_list as $key => $item){
            $txt = "    ".$item['language_key'].": \"".addslashes($item['language_value'])."\"";
            if ($key<count($lang_list)-1){
                $txt .= ",\r\n";
            }
            else{
                $txt .= "\r\n";
            }
            //写入文件
            fwrite($langfile, $txt);
        }
    }
    fwrite($langfile, "};\r\n");
    fclose($langfile);
}

unset($params, $where, $project_info);
//返回结果
return json(0,'把语言包内容写入JS文件成功');
