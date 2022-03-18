<?php
/*
 * 添加一批UUID
 * 运行方式：/usr/local/php7.4.16/bin/php /data/web/passport.yiluphp.com/yilu batch_insert_uuid "count=200&disuse_old=1"
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2022/03/18
 * Time: 21:57
 */

$count = input::I()->get_int('count',100000);
$disuse_old = input::I()->get_int('disuse_old',0);
$disuse_old = empty($disuse_old)?false:true;
echo "正在添加\r\n";
if(logic_uuid::I()->batch_insert_uuid($count, $disuse_old)){
    echo "添加成功！\r\n";
}
else{
    echo "添加失败！\r\n";
}

exit("执行完成\r\n\r\n");