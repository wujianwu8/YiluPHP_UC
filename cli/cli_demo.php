<?php
/*
 * 这是一个CLI命令操作的例子，你可以删除这个demo
 * 运行方式如：/usr/local/php7/bin/php /data/web/www.yiluphp.com/cli/cli_demo.php "user_id=88"
 * 这个命令中/usr/local/php7/bin/php是你的PHP安装位置
 * 这是你的文件存放位置：/data/web/www.yiluphp.com/
 * 这是传两个参数user_id和page过去，如果没有参数可以不写 "user_id=88&page=1"
 * OneWayPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/24
 * Time: 21:45
 */

if(!isset($_SERVER['REQUEST_URI'])){
    $the_argv = $argv;
    unset($the_argv[0]);
    //获取命令行内容
    $_SERVER['REQUEST_URI'] = 'php '.$argv[0].' "'.implode('" "', $the_argv).'"';
}
$project_root = explode('/cli/', __FILE__);
$project_root = $project_root[0].'/';
include_once($project_root.'public/index.php');

//接下来你可以像在controller里一样编程
$user_id = $app->input->get_trim('user_id');
$page = $app->input->get_int('page');
var_dump($user_id, $page);
exit("\r\n完成\r\n\r\n");