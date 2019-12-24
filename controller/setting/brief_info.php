<?php
/**
 * @name 当前登录用户的简短信息
 * @desc uid、nickname、avatar、gender
 * @method GET
 * @uri /setting/brief_info
 * @return JSON
 */

return_json(0, $app->lang('successful_get'),
    [
        'user_info' => $self_info,
    ]
);