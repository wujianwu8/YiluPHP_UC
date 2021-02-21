<?php
/*
 * 清除所有REDIS缓存
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 21:45
 */
if(!isset($_SERVER['REQUEST_URI'])){
    $the_argv = $argv;
    unset($the_argv[0]);
    $_SERVER['REQUEST_URI'] = 'php '.$argv[0].' "'.implode('" "', $the_argv).'"';
}
if (!defined('APP_PATH')){
    $project_root = explode(DIRECTORY_SEPARATOR.'cli'.DIRECTORY_SEPARATOR, __FILE__);
    //项目的根目录，最后包含一个斜杠
    define('APP_PATH', $project_root[0].DIRECTORY_SEPARATOR);
    unset($project_root);
}
include_once(APP_PATH.'public'.DIRECTORY_SEPARATOR.'index.php');

//删除所有已用的Nickname
redis_y::I()->del(REDIS_KEY_ALL_NICKNAME);

//删除所有已用的身份
redis_y::I()->del(REDIS_KEY_ALL_IDENTITY);

exit("\r\n完成\r\n\r\n");