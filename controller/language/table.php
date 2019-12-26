<?php
/**
 * @group 语言包
 * @name 编辑项目的语言包内容的页面
 * @desc
 * @method GET
 * @uri /language/table/{project_id}
 * @param integer project_id 项目ID 必选 项目ID
 * @param string keyword 搜索关键字 可选
 * @param integer page 页码 可选 默认为1
 * @param integer page_size 每页条数 可选 默认为10
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "把语言包内容写入PHP文件成功"
 * }
 * @exception
 *  0 把语言包内容写入PHP文件成功
 *  1 把语言包内容写入PHP文件失败
 *  2 项目ID参数有误
 *  3 项目不存在
 *  4
 *  5
 *  6
 *  7
 *  8
 */

if (!$app->logic_permission->check_permission('user_center:view_project_lang_list')) {
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
$project_info['language_types'] = explode(',', $project_info['language_types']);

$page = $app->input->get_int('page',1);
$page_size = $app->input->get_int('page_size',15);
$page_size>500 && $page_size = 500;
$page_size<1 && $page_size = 1;
$keyword = $app->input->get_trim('keyword',null);

$data_list = [];
$data_count = 0;
//获取项目的去重后的、已排序好的语言键名
if ($language_key_list = $app->model_language_value->paging_select_project_distinct_language_key($project_info['project_key'], $page, $page_size, $keyword)){
    $language_key_list = array_column($language_key_list, 'language_key');
    //获取各语言的内容
    foreach ($project_info['language_types'] as $lang){
        $value_list = $app->model_language_value->select_all([
            'project_key' => $project_info['project_key'],
            'language_type' => $lang,
            'language_key' => [
                'symbol' => 'IN',
                'value' => $language_key_list,
            ],
        ],'','id,language_key,language_value,output_type');
        $temp = [];
        $output_type = [];
        foreach ($value_list as $item){
            $temp[$item['language_key']] = [
                'id' => $item['id'],
                'language_value' => $item['language_value'],
            ];
            $tmp = explode('-', $item['output_type']);
            if(isset($output_type[$item['language_key']])){
                $output_type[$item['language_key']] = [];
            }
            foreach ($tmp as $type){
                if ($type){
                    $output_type[$item['language_key']][] = $type;
                }
            }
        }
        $value_list = $temp;
        unset($temp);
        foreach ($language_key_list as $language_key){
            if (isset($value_list[$language_key])){
                $data_list[$language_key][$lang] = $value_list[$language_key];
                $data_list[$language_key]['output_type'] = $output_type[$language_key];
            }
            else{
                $data_list[$language_key][$lang] = null;
            }
        }
    }
    $data_count = $app->model_language_value->count_project_distinct_language_key($project_info['project_key'], $keyword);
    unset($value_list);
}

unset($params, $where, $keyword);
return_result('language/table', [
    'project_info' => $project_info,
    'data_list' => $data_list,
    'data_count' => $data_count,
    'page' => $page,
    'page_size' => $page_size,
]);