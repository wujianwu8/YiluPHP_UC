<?php
/**
 * @group 用户
 * @name 当前登录用户的简短信息
 * @desc uid、nickname、avatar、gender
 * @method GET
 * @uri /setting/brief_info
 * @return JSON
 * {
 *      code: 0
 *      ,data: {
 *          "user_info":{
 *              'avatar':'http://www.yiluphp.com/sss..fff.jpg',
 *              'uid':3568,
 *              'nickname':'中国人'
 *          }
 *      }
 *      ,msg: "获取成功"
 * }
 */

return json(0, YiluPHP::I()->lang('successful_get'),
    [
        'user_info' => $self_info,
    ]
);