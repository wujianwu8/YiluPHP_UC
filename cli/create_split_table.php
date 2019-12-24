<?php
/*
 * 创建分表
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

$tables_sql = [
	[
		'name' => 'user',
		'count' => 100,
		'sql' =>"CREATE TABLE `{{table_name_replacer}}` (
  `uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('female','male') CHARACTER SET utf8 NOT NULL DEFAULT 'female',
  `password` char(32) CHARACTER SET utf8 DEFAULT NULL,
  `salt` char(13) CHARACTER SET utf8 DEFAULT NULL COMMENT '密码加盐',
  `birthday` int(10) DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '用户状态，0被锁，1正常',
  `avatar` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '用户头像',
  `country` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `province` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `city` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `mtime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间戳',
  `ctime` int(10) NOT NULL DEFAULT '0' COMMENT '创建的时间戳',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `idx_nickname` (`nickname`) USING HASH
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户的基本资料'",
	],
	[
		'name' => 'user_identity',
		'count' => 100,
		'sql' =>"CREATE TABLE `{{table_name_replacer}}` (
  `uid` bigint(20) NOT NULL,
  `type` char(5) NOT NULL COMMENT '身份类型，如：INNER表示内部账号(包括邮箱、用户名、手机号)，微信WX，QQ',
  `identity` varchar(100) NOT NULL COMMENT '登录名或第三方的唯一OPENID',
  `access_token` varchar(1024) NOT NULL DEFAULT '',
  `expires_at` bigint(12) NOT NULL DEFAULT '0',
  `refresh_token` varchar(128) NOT NULL DEFAULT '',
  `ctime` int(10) NOT NULL,
  PRIMARY KEY (`type`,`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='登录名或第三方用户的标识'",
	],
];

$connection = 'default';

foreach($tables_sql as $table){
	for($i=0; $i<$table['count']; $i++) {
		$sub_connection = $connection.'_'.$i;
		if(!isset($config['mysql'][$sub_connection])){
			$sub_connection = $connection;
		}
		$sub_table = $table['name'].'_'.$i;

		//删除原表
		$sql="DROP TABLE IF EXISTS `".$sub_table."`";
		$stmt = $app->mysql($sub_connection)->prepare($sql);
		$stmt->execute();

		$sql = "SELECT table_name FROM information_schema.TABLES WHERE table_name ='".$sub_table."'";
		$stmt = $app->mysql($sub_connection)->prepare($sql);
		$stmt->execute();
		if(!$stmt->fetch(PDO::FETCH_ASSOC)) {
//			var_dump($res);
//			die;

			$sql = str_replace('{{table_name_replacer}}', $sub_table, $table['sql']);
			$stmt = $app->mysql($sub_connection)->prepare($sql);
			if (!$stmt->execute()) {
				echo "执行失败" . $table['name'] . "\r＼n";
			}
		}
	}

	//删除所有
	$app->redis()->del(REDIS_KEY_ALL_IDENTITY);
}

exit("\r\n完成\r\n\r\n");