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

if (!$app->logic_permission->check_permission('user_center:delete_application')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
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
    return_code(3,'应用ID填写有误');
}
if (strpos($params['app_id'], 'grant_')===0){
    unset($params);
    return_code(4,'应用ID填写有误');
}
if (!$check=$app->model_application->find_table(['app_id' => $params['app_id']])){
    unset($params, $check);
    return_code(5,'应用不存在');
}
if ($check['is_fixed']){
    unset($params, $check);
    return_code(6,'固定应用不可删除');
}

unset($check);

//删除应用
if(false === $app->logic_application->delete_application($params['app_id'])){
    unset($params, $where, $data);
    return_code(1, '删除失败');
}

unset($params, $where, $data);
//返回结果
return_json(0,'删除成功');
