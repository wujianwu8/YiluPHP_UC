<?php
/*
 * 清除所有REDIS缓存
 * OneWayPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/24
 * Time: 21:45
 */
if(!isset($_SERVER['REQUEST_URI'])){
    $the_argv = $argv;
    unset($the_argv[0]);
    $_SERVER['REQUEST_URI'] = 'php '.$argv[0].' "'.implode('" "', $the_argv).'"';
}
$project_root = explode('/cli/', __FILE__);
$project_root = $project_root[0].'/';
include_once($project_root.'public/index.php');

//删除所有已用的Nickname
$app->redis()->del(REDIS_KEY_ALL_NICKNAME);

//删除所有已用的身份
$app->redis()->del(REDIS_KEY_ALL_IDENTITY);

exit("\r\n完成\r\n\r\n");