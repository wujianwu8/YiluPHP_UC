<?php
/**
 * @group 应用系统
 * @name 编辑应用
 * @desc
 * @method GET
 * @uri /application/edit
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:edit_application')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'app_id' => 'required|string|return',
    ],
    [
        'app_id.*' => '应用ID参数错误',
    ],
    [
        'app_id.*' => 1,
    ]);

if (!$app_info=model_application::I()->find_table(['app_id'=>$params['app_id']])){
    return code(2, '应用不存在');
}
return result('application/edit', [
    'app_info' => $app_info
]);