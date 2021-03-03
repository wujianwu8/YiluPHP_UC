<?php
/**
 * @group 应用系统
 * @name 保存新应用
 * @desc
 * @method POST
 * @uri /application/save_add
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
 *  7 应用ID已经存在，请更换一个
 *  8 白名单IP设置错误，请检查
 *  9 应用ID填写有误
 * 10 应用ID不可使用，请更换一个
 */

if (!logic_permission::I()->check_permission('user_center:add_application')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'app_id' => 'required|trim|string|min:3|max:20|return',
        'app_name' => 'required|trim|string|min:3|max:30|return',
        'status' => 'required|integer|min:0|max:1|return',
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

if (!isset($params['app_white_ip']) || trim($params['app_white_ip'])==''){
    $params['app_white_ip'] = '';
}
else {
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
}

if (preg_match('/^[a-zA-Z0-9_]{3,20}$/', $params['app_id'], $matches)==false){
    unset($params);
    return code(9,'应用ID填写有误');
}
if (strpos($params['app_id'], 'grant_')===0){
    unset($params);
    return code(10,'应用ID不可使用，请更换一个');
}
if ($check=model_application::I()->find_table(['app_id' => $params['app_id']])){
    unset($params, $check);
    return code(7,'应用ID已经存在，请更换一个');
}
unset($check);

//保存应用入库
$params['uid'] = $self_info['uid'];
$params['app_secret'] = md5(json_encode($params).microtime().uniqid());
$params['ctime'] = time();
if(false === model_application::I()->insert_table($params)){
    unset($params);
    return code(1, '保存失败');
}

$permission_keys = model_permission::I()->permission_control_keys();
foreach ($permission_keys as $permission_key => $name){
    model_permission::I()->insert_table([
        'permission_name' => $name,
        'permission_key' => $permission_key,
        'app_id' => $params['app_id'],
        'is_fixed' => 1,
    ]);
    if($permission = model_permission::I()->find_table(['app_id' => $params['app_id'],'permission_key' => $permission_key], 'permission_id')){
        $permission_id = model_user_permission::I()->insert_table([
            'uid' => $self_info['uid'],
            'permission_id' => $permission['permission_id'],
        ]);
    }
}

unset($params, $permission_keys, $permission_key, $name, $permission_id);
//返回结果
return json(CODE_SUCCESS,YiluPHP::I()->lang('save_successfully'));
