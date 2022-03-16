<?php
/*
 * 这是一个CLI命令操作的例子，你可以删除这个demo
 * 运行方式如：/usr/local/php7/bin/php /data/web/www.yiluphp.com/yilu cli_demo "user_id=88&page=1"
 * 这个命令中/usr/local/php7/bin/php是你的PHP安装位置
 * 这是你的文件存放位置：/data/web/www.yiluphp.com/
 * 这是CLI命令的入口：yilu
 * 这里传递了两个参数user_id和page过去，如果没有参数可以不传
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * Date: 2021.01.01
 * Time: 11:19
 */

$user_id = input::I()->get_trim('user_id');
$page = input::I()->get_int('page');
var_dump($user_id, $page);
exit("\r\n完成\r\n\r\n");