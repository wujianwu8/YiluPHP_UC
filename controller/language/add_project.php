<?php
/**
 * @group 语言包
 * @name 添加语言包项目页
 * @desc
 * @method GET
 * @uri /language/add_project
 * @return HTML
 */

if (!logic_permission::I()->check_permission('user_center:add_lang_project')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}


return result('language/add_project');