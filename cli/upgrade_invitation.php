<?php
/*
 * 邀请机制升级脚本
 * 运行命令：/你的php目录/php /你的项目目录/yilu upgrade_invitation
 *
 * 执行完成后请务必重建索引和缓存：
 * php /你的项目目录/yilu build_necessary_redis_data
 */

$time = time();
$tables = ['user'];
if (!empty($GLOBALS['config']['split_table'])) {
    for ($i = 0; $i < 100; $i++) {
        $tables[] = 'user_' . $i;
    }
}
$tables = array_unique($tables);

foreach ($tables as $table_name) {
    $connection = 'default';
    if ($table_name !== 'user' && preg_match('/_(\d+)$/', $table_name, $matches)) {
        $sub_connection = 'default_' . $matches[1];
        if (isset($GLOBALS['config']['mysql'][$sub_connection])) {
            $connection = $sub_connection;
        }
    }

    $columns = [
        'register_invitation_link_id' => "ALTER TABLE `{$table_name}` ADD COLUMN `register_invitation_link_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '注册时的邀请链接ID' AFTER `ctime`",
        'register_inviter_uid' => "ALTER TABLE `{$table_name}` ADD COLUMN `register_inviter_uid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '注册时的邀请人UID' AFTER `register_invitation_link_id`",
    ];

    foreach ($columns as $column => $sql) {
        $stmt = mysql::I($connection)->prepare("SHOW COLUMNS FROM `{$table_name}` LIKE '{$column}'");
        $stmt->execute();
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            mysql::I($connection)->prepare($sql)->execute();
        }
    }

    $stmt = mysql::I($connection)->prepare("SHOW INDEX FROM `{$table_name}` WHERE Key_name='idx_register_inviter'");
    $stmt->execute();
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        mysql::I($connection)->prepare("ALTER TABLE `{$table_name}` ADD INDEX `idx_register_inviter` (`register_inviter_uid`) ")->execute();
    }
}

$stmt = mysql::I()->prepare("SHOW TABLES LIKE 'invitation_link'");
$stmt->execute();
if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
    $sql = "CREATE TABLE `invitation_link` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `uid` bigint(20) unsigned NOT NULL COMMENT '所属用户ID',
      `scene` varchar(32) NOT NULL COMMENT '邀请场景，如register',
      `invite_code` varchar(64) NOT NULL COMMENT '邀请码',
      `cookie_ttl` int(10) unsigned NOT NULL DEFAULT '1296000' COMMENT 'cookie有效期，秒，默认15天',
      `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
      `mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后修改时间',
      `ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_invite_code` (`invite_code`),
      UNIQUE KEY `uniq_uid_scene` (`uid`,`scene`),
      KEY `idx_scene` (`scene`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邀请链接'";
    mysql::I()->prepare($sql)->execute();
}

$permissions = [
    ['grant_grant_view_invitation_link', 'lang_view_invitation_link'],
    ['grant_view_invitation_link', 'lang_view_invitation_link'],
    ['view_invitation_link', 'lang_view_invitation_link'],
    ['grant_grant_add_invitation_link', 'lang_add_invitation_link'],
    ['grant_add_invitation_link', 'lang_add_invitation_link'],
    ['add_invitation_link', 'lang_add_invitation_link'],
    ['grant_grant_edit_invitation_link', 'lang_edit_invitation_link'],
    ['grant_edit_invitation_link', 'lang_edit_invitation_link'],
    ['edit_invitation_link', 'lang_edit_invitation_link'],
];
foreach ($permissions as $item) {
    if (!model_permission::I()->find_table(['app_id' => 'user_center', 'permission_key' => $item[0]], 'permission_id')) {
        model_permission::I()->insert_table([
            'app_id' => 'user_center',
            'permission_key' => $item[0],
            'permission_name' => $item[1],
            'description' => '',
            'is_fixed' => 1,
        ]);
    }
}

$menus = [
    [
        'parent_menu' => 0,
        'type' => 'SYSTEM',
        'icon' => 'fa-link',
        'lang_key' => 'invitation_link',
        'position' => 'LEFT',
        'href' => '/invitation/list',
        'target' => '',
        'link_class' => '',
        'weight' => 450,
        'permission' => 'user_center:view_invitation_link',
        'active_preg' => '\\/invitation\\/list',
        'ctime' => $time,
    ],
    [
        'parent_menu' => 0,
        'type' => 'SYSTEM',
        'icon' => 'fa-share-square-o',
        'lang_key' => 'invite_register',
        'position' => 'LEFT',
        'href' => '/invitation/register',
        'target' => '',
        'link_class' => '',
        'weight' => 400,
        'permission' => '',
        'active_preg' => '\\/invitation\\/register',
        'ctime' => $time,
    ],
];
foreach ($menus as $menu) {
    if ($menu_info = model_menus::I()->find_table(['href' => $menu['href']], 'id')) {
        model_menus::I()->update_table(['id' => $menu_info['id']], $menu);
    }
    else {
        model_menus::I()->insert_table($menu);
    }
}

$admin_permissions = model_permission::I()->select_all([
    'app_id' => 'user_center',
    'permission_key' => [
        'symbol' => 'IN',
        'value' => array_column($permissions, 0),
    ],
], '', 'permission_id');
foreach ($admin_permissions as $item) {
    if (!model_user_permission::I()->find_table(['uid' => 1, 'permission_id' => $item['permission_id']], 'uid')) {
        model_user_permission::I()->insert_table([
            'uid' => 1,
            'permission_id' => $item['permission_id'],
        ]);
    }
}

redis_y::I()->del(REDIS_KEY_ALL_MENUS);
redis_y::I()->del(REDIS_KEY_USER_PERMISSION . '1');
redis_y::I()->del(REDIS_KEY_USER_PERMISSION . '1_user_center');

exit("\r\n邀请机制升级完成。\r\n请继续执行：php /你的项目目录/yilu build_necessary_redis_data\r\n\r\n");
