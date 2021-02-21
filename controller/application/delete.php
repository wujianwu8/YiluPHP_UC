<?php
/**
 * @group 应用系统
 * @name 删除应用
 * @desc
 * @method POST
 * @uri /application/delete
 * @param string app_id 应用ID 必选
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "删除成功"
 * }
 * @exception
 *  0 删除成功
 *  1 删除失败
 *  2 应用ID填写有误
 *  3 应用ID填写有误
 *  4 应用ID填写有误
 *  5 应用不存在
 *  6 固定应用不可删除
 */

if (!logic_permission::I()->check_permission('user_center:delete_application')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'app_id' => 'required|trim|string|min:3|max:20|return',
    ],
    [
        'app_id.*' => '应用ID填写有误',
    ],
    [
        'app_id.*' => 2,
    ]);

if (preg_match('/^[a-zA-Z0-9_]{3,20}$/', $params['app_id'], $matches)==false){
    unset($params);
    return code(3,'应用ID填写有误');
}
if (strpos($params['app_id'], 'grant_')===0){
    unset($params);
    return code(4,'应用ID填写有误');
}
if (!$check=model_application::I()->find_table(['app_id' => $params['app_id']])){
    unset($params, $check);
    return code(5,'应用不存在');
}
if ($check['is_fixed']){
    unset($params, $check);
    return code(6,'固定应用不可删除');
}

unset($check);

//删除应用
if(false === logic_application::I()->delete_application($params['app_id'])){
    unset($params, $where, $data);
    return code(1, '删除失败');
}

unset($params, $where, $data);
//返回结果
return json(0,'删除成功');
