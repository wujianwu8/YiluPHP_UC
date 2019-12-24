<?php
/**
 * @name 添加语言包项目页
 * @desc
 * @method GET
 * @uri /language/add_project
 * @return HTML
 */

if (!$app->logic_permission->check_permission('user_center:add_lang_project')) {
    return_code(100, $app->lang('not_authorized'));
}


return_result('language/add_project');