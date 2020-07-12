<?php
$options = $config['mysql']['default'];
$options['option'] = [
    PDO::ATTR_PERSISTENT => true,   //开启长连接的方法
]
$pdo = connectMysql($options);

var_dump($config['mysql']['default']);