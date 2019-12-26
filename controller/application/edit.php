<?php
/**
 * @group 应用系统
 * @name 编辑应用
 * @desc
 * @method GET
 * @uri /application/edit
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:edit_application')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'app_id' => 'required|string|return',
    ],
    [
        'app_id.*' => '应用ID参数错误',
    ],
    [
        'app_id.*' => 1,
    ]);

if (!$app_info=$app->model_application->find_table(['app_id'=>$params['app_id']])){
    return_code(2, '应用不存在');
}
return_result('application/edit', [
    'app_info' => $app_info
]);