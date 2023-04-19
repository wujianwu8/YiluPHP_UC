<?php
/**
 * @name 示例页
 * @desc
 * @method GET
 * @uri /demo/index
 * @param integer id 页码 可选 示例
 * @return HTML
 */

//这里做访问权限控制
//if (!model_user_center::I()->check_user_permission($self_info['uid'], 'view_demo_system')) {
//    return_code(CODE_NO_AUTHORIZED, YiluPHP::I()->lang('not_authorized'));
//    throw new validate_exception(YiluPHP::I()->lang('not_authorized'), CODE_NO_AUTHORIZED);
//}

$id = input::I()->get_int('id');

return result('demo/index');