<?php
/*
 * 清除所有REDIS缓存
 * 运行命令：/usr/local/php7.4.16/bin/php /data/web/passport.yiluphp.com/yilu clear_db_data
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 21:45
 */

//循环所有的库
foreach($config['mysql'] as $connection => $mysql){
    print_r($connection."\r\n");
    //读取库中所有表
    $sql = "select table_name from information_schema.tables where table_schema='yilu_uc' and table_type='base table'";
        $stmt = mysql::I($connection)->prepare($sql);
        $stmt->execute();
//		PDO::FETCH_ASSOC          从结果集中获取以列名为索引的关联数组。
//  	PDO::FETCH_NUM             从结果集中获取一个以列在行中的数值偏移量为索引的值数组。
//  	PDO::FETCH_BOTH            这是默认值，包含上面两种数组。
//  	PDO::FETCH_OBJ               从结果集当前行的记录中获取其属性对应各个列名的一个对象。
//  	PDO::FETCH_BOUND        使用fetch()返回TRUE，并将获取的列值赋给在bindParm()方法中指 定的相应变量。
//  	PDO::FETCH_LAZY            创建关联数组和索引数组，以及包含列属性的一个对象，从而可以在这三种接口中任选一种。
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($res as $table){
        $sql = 'truncate `'.$table['table_name'].'`';
//        var_dump($sql);die;
        mysql::I($connection)->exec($sql);
//        $stmt = mysql::I($connection)->prepare($sql);
//        $stmt->execute();
    }
}

exit("\r\n完成\r\n\r\n");