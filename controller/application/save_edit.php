<?php
/**
 * @group 应用系统
 * @name 保存修改后的应用信息
 * @desc
 * @method POST
 * @uri /application/save_edit
 * @param string app_id 应用ID 必选
 * @param string app_name 应用名称 必选
 * @param integer status 状态 必选 状态：0不可用，1可用
 * @param string app_white_ip IP白名单 必选 应用服务器的IP，多个IP使用半角逗号或换行分隔
 * @param string index_url 应用首页 可选 应用首页地址
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 保存成功
 *  1 保存失败
 *  2 应用ID填写有误
 *  3 应用名称填写有误
 *  4 状态选择有误
 *  5 白名单IP设置错误，请检查
 *  6 应用首页地址填写有误
 *  7 应用不存在
 *  8 白名单IP设置错误，请检查
 *  9 应用ID填写有误
 * 10 应用ID填写有误
 */

if (!logic_permission::I()->check_permission('user_center:edit_application')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'app_id' => 'required|trim|string|min:3|max:20|return',
        'app_name' => 'trim|string|min:3|max:30|return',
        'status' => 'integer|min:0|max:1|return',
        'app_white_ip' => 'trim|string|max:2000|return',
        'index_url' => 'trim|string|min:6|max:200|return',
    ],
    [
        'app_id.*' => '应用ID填写有误',
        'app_name.*' => '应用名称填写有误',
        'status.*' => '状态选择有误',
        'app_white_ip.*' => '白名单IP设置错误，请检查',
        'index_url.*' => '应用首页地址填写有误',
    ],
    [
        'app_id.*' => 2,
        'app_name.*' => 3,
        'status.*' => 4,
        'app_white_ip.*' => 5,
        'index_url.*' => 6,
    ]);

$data = [];
if (!isset($params['app_white_ip']) || trim($params['app_white_ip'])=='') {
    $data['app_white_ip'] = '';
}
else{
    if (preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}[\,|\r|\n]*)+$/', $params['app_white_ip'], $matches) == false) {
        unset($params);
        return code(8, '白名单IP设置错误，请检查');
    }

    $tmp = preg_replace('/[\r|\n|\,]+/', ',', $params['app_white_ip']);
    $tmp = explode(',', $tmp);
    $tmp2 = array_unique($tmp);
    if (count($tmp) != count($tmp2)) {
        unset($params);
        return code(11, 'IP白名单重复了：' . implode(',', array_diff_assoc($tmp, $tmp2)));
    }
    unset($tmp, $tmp2);
    $data['app_white_ip'] = $params['app_white_ip'];
}

if (preg_match('/^[a-zA-Z0-9_]{3,20}$/', $params['app_id'], $matches)==false){
    unset($params);
    return code(9,'应用ID填写有误');
}
if (strpos($params['app_id'], 'grant_')===0){
    unset($params);
    return code(10,'应用ID填写有误');
}
if (!$check=model_application::I()->find_table(['app_id' => $params['app_id']])){
    unset($params, $check);
    return code(7,'应用不存在');
}
unset($check);

if (isset($params['app_name'])) {
    $data['app_name'] = $params['app_name'];
}

if (isset($params['status'])) {
    $data['status'] = $params['status'];
}

if (isset($params['index_url'])) {
    $data['index_url'] = $params['index_url'];
}

$where = [
    'app_id' => $params['app_id']
];
if (count($data)==0){
    return json(0,YiluPHP::I()->lang('save_successfully'));
}
//保存应用入库
if(false === model_application::I()->update_table($where, $data)){
    unset($params, $where, $data);
    return code(1, '保存失败');
}

unset($params, $where, $data);
//返回结果
return json(0,YiluPHP::I()->lang('save_successfully'));
