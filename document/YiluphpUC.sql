

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `application`
-- ----------------------------
DROP TABLE IF EXISTS `application`;
CREATE TABLE `application` (
  `app_id` varchar(20) NOT NULL COMMENT '由字母、数字、下划线组成的应用ID',
  `app_name` varchar(30) NOT NULL COMMENT '方便人眼识别的应用名称',
  `uid` bigint(20) NOT NULL COMMENT '创建人用户ID，0则表示为固定应用',
  `index_url` varchar(200) NOT NULL DEFAULT '' COMMENT '应用首页URL',
  `app_secret` char(32) NOT NULL DEFAULT '',
  `app_white_ip` varchar(2000) NOT NULL DEFAULT '' COMMENT '应用系统的服务器IP白名单，多个IP使用半角逗号分隔',
  `ctime` int(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0不可用，1可用',
  `is_fixed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '固定应用不可删除，0非固定应用，1为固定应用',
  PRIMARY KEY (`app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `application`
-- ----------------------------
BEGIN;
INSERT INTO `application` VALUES ('user_center', '用户中心', '1', '', '0477dc28a057ac4dd7e70cfbee167772', '127.0.0.1', '1570774279', '1', '0');
COMMIT;

-- ----------------------------
--  Table structure for `email_code_record`
-- ----------------------------
DROP TABLE IF EXISTS `email_code_record`;
CREATE TABLE `email_code_record` (
  `email_code_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL COMMENT '手机区号',
  `plat` varchar(20) NOT NULL DEFAULT '' COMMENT '发短信的平台名称',
  `client_ip` varchar(20) DEFAULT '',
  `vk` varchar(32) DEFAULT '' COMMENT '存在客户端的访客唯一标识',
  `refuse_reason` varchar(2000) DEFAULT '' COMMENT '被拒绝原因',
  `mark` varchar(1000) DEFAULT '' COMMENT '其它的备注信息',
  `is_send` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否发送成功，0未发送，1已发送',
  `is_used` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '此验证码是否已使用？0未使用，1已使用',
  `ctime` int(10) unsigned NOT NULL COMMENT '发送时间',
  `mtime` int(10) unsigned NOT NULL COMMENT '最后修改时间，即使用时间',
  PRIMARY KEY (`email_code_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `file`
-- ----------------------------
DROP TABLE IF EXISTS `file`;
CREATE TABLE `file` (
  `file_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(200) NOT NULL,
  `type` enum('avatar') NOT NULL,
  `create_at` int(10) NOT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `uid` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `group`
-- ----------------------------
DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `group_id` int(10) unsigned NOT NULL,
  `group_name` varchar(20) NOT NULL,
  `parent_group_id` int(10) NOT NULL DEFAULT '0',
  `description` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `language_project`
-- ----------------------------
DROP TABLE IF EXISTS `language_project`;
CREATE TABLE `language_project` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(30) NOT NULL DEFAULT '' COMMENT '项目键名，仅由字母、数字、下划线组成',
  `project_name` varchar(40) NOT NULL DEFAULT '' COMMENT '项目名称',
  `description` varchar(200) NOT NULL DEFAULT '' COMMENT '描述',
  `file_dir` varchar(200) NOT NULL DEFAULT '' COMMENT '保存PHP翻译包的目录',
  `js_file_dir` varchar(200) NOT NULL DEFAULT '' COMMENT '保存JS文件语言包的目录',
  `language_types` varchar(200) NOT NULL DEFAULT '' COMMENT '语言的种类，多个语种使用半角逗号分隔，如：zh,en',
  `ctime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `projectKey` (`project_key`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `language_project`
-- ----------------------------
BEGIN;
INSERT INTO `language_project` VALUES ('1', 'user_center', 'user_center', '', '/data/web/passport.yiluphp.com/lang', '/data/web/passport.yiluphp.com/static/js/language', 'cn,en', '0');
COMMIT;

-- ----------------------------
--  Table structure for `language_value`
-- ----------------------------
DROP TABLE IF EXISTS `language_value`;
CREATE TABLE `language_value` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_key` varchar(30) NOT NULL,
  `language_type` varchar(10) NOT NULL,
  `language_key` varchar(150) NOT NULL COMMENT '语言键仅由字母、数字、下划线组成',
  `language_value` text,
  `output_type` varchar(30) NOT NULL DEFAULT '-PHP-' COMMENT '输出语言包类型，多个类型使用中杆分隔，前后需要加中杆便于搜索，如-PHP-JS-',
  `ctime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `typeLangKey` (`project_key`,`language_type`,`language_key`),
  KEY `outputType` (`output_type`)
) ENGINE=InnoDB AUTO_INCREMENT=17962 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `menus`
-- ----------------------------
DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_menu` int(10) NOT NULL DEFAULT '0' COMMENT '父级菜单的ID',
  `type` varchar(10) NOT NULL DEFAULT 'SYSTEM' COMMENT 'SYSTEM系统自带,CUSTOMIZE自定义',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '菜单前的样式或HTML代码',
  `lang_key` varchar(50) NOT NULL,
  `position` varchar(10) NOT NULL DEFAULT 'TOP' COMMENT 'TOP或LEFT',
  `href` varchar(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `target` varchar(20) NOT NULL DEFAULT '' COMMENT '连接目标，_blank',
  `link_class` varchar(20) NOT NULL DEFAULT '' COMMENT 'A链接的附加样式名',
  `weight` smallint(4) NOT NULL DEFAULT '500',
  `permission` varchar(64) NOT NULL DEFAULT '' COMMENT '访问所需权限',
  `active_preg` varchar(100) NOT NULL DEFAULT '' COMMENT '选中菜单的正则表达式规则，不填写则完全等于href即选中',
  `ctime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `menus`
-- ----------------------------
BEGIN;
INSERT INTO `menus` VALUES ('2', '4', 'CUSTOMIZE', '', 'menu_account_setting', 'TOP', '/setting/user_info', '', 'ajax_main_content', '300', '', '\\/setting\\/user_info.*', '1569243258'), ('3', '0', 'CUSTOMIZE', '', 'menu_account_setting', 'TOP', '/setting/user_info', '', 'ajax_main_content', '200', '', '\\/setting\\/user_info.*', '1569243259'), ('4', '0', 'CUSTOMIZE', '', 'nav-user-avatar', 'TOP', '', '', 'ajax_main_content', '500', '', '', '1569243260'), ('5', '4', 'CUSTOMIZE', '', 'menu_modify_avatar', 'TOP', '/setting/modify_avatar', '', 'ajax_main_content', '350', '', '\\/setting\\/modify_avatar.*', '1569243261'), ('8', '0', 'CUSTOMIZE', '', 'menu_change_password', 'TOP', '/setting/modify_password', '', 'ajax_main_content', '300', '', '\\/setting\\/modify_password.*', '1569243264'), ('9', '0', 'CUSTOMIZE', '', 'menu_modify_avatar', 'TOP', '/setting/modify_avatar', '', 'ajax_main_content', '250', '', '\\/setting\\/modify_avatar.*', '1569243265'), ('12', '4', 'CUSTOMIZE', '', 'menu_change_password', 'TOP', '/setting/modify_password', '', 'ajax_main_content', '400', '', '\\/setting\\/modify_password.*', '1569243268'), ('13', '4', 'CUSTOMIZE', '', 'menu_sign_out', 'TOP', '/sign/out', '', '', '500', '', '\\/sign\\/out', '1569243269'), ('14', '0', 'CUSTOMIZE', '', 'menu_user_manage', 'LEFT', '', '', 'ajax_main_content', '200', '', 'none', '1569243270'), ('15', '14', 'CUSTOMIZE', 'fa-address-book', 'menu_user_list', 'LEFT', '/user/list', '', 'ajax_main_content', '200', 'user_center:view_user_list', '\\/user\\/list.*', '1569243271'), ('16', '14', 'CUSTOMIZE', 'fa-user-times', 'menu_blocked_user', 'LEFT', '/user/forbidden', '', 'ajax_main_content', '250', 'user_center:view_block_user_list', '\\/user\\/forbidden.*', '1569243272'), ('17', '14', 'CUSTOMIZE', 'fa-user-secret', 'menu_complained_user', 'LEFT', '/complaint/list', '', 'ajax_main_content', '300', 'user_center:view_complaint_user_list', '\\/complaint.*', '1569243273'), ('18', '0', 'CUSTOMIZE', 'fa-th', 'menu_application_manage', 'LEFT', '/application/list', '', 'ajax_main_content', '250', 'user_center:view_application_list', '\\/application\\/.*', '1569243274'), ('19', '0', 'CUSTOMIZE', 'fa-envelope', 'menu_user_feedback', 'LEFT', '/feedback/list', '', 'ajax_main_content', '350', 'user_center:view_feedback', '\\/feedback.*', '1569243275'), ('20', '0', 'CUSTOMIZE', 'fa-bars', 'menu_custom_menu', 'LEFT', '/menus/list', '', 'ajax_main_content', '301', 'user_center:view_customize_menu', '\\/menus\\/.*', '1569243276'), ('21', '14', 'CUSTOMIZE', 'fa-user-o', 'menu_role_manage', 'LEFT', '/role/list', '', 'ajax_main_content', '230', 'user_center:view_role_list', '\\/role\\/.*', '1570778382'), ('22', '0', 'CUSTOMIZE', 'fa-language', 'menu_language_pack', 'LEFT', '/language/project', '', 'ajax_main_content', '270', 'user_center:view_lang_project_list', '\\/language\\/.*', '1570961480');
COMMIT;

-- ----------------------------
--  Table structure for `permission`
-- ----------------------------
DROP TABLE IF EXISTS `permission`;
CREATE TABLE `permission` (
  `permission_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(20) NOT NULL COMMENT '应用ID',
  `permission_key` varchar(40) NOT NULL DEFAULT '',
  `permission_name` varchar(40) NOT NULL,
  `description` varchar(200) NOT NULL DEFAULT '',
  `is_fixed` tinyint(1) DEFAULT '0' COMMENT '0非固定权限，1为固定权限不可删除',
  PRIMARY KEY (`permission_id`),
  UNIQUE KEY `appPermission` (`app_id`,`permission_key`)
) ENGINE=InnoDB AUTO_INCREMENT=237 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `permission`
-- ----------------------------
BEGIN;
INSERT INTO `permission` VALUES ('48', 'user_center', 'view_permission', 'lang_view_permission', '', '1'), ('49', 'user_center', 'add_permission', 'lang_add_permission', '', '1'), ('50', 'user_center', 'edit_permission', 'lang_edit_permission', '', '1'), ('51', 'user_center', 'delete_permission', 'lang_delete_permission', '', '1'), ('52', 'user_center', 'grant_view_permission', 'lang_view_permission', '', '1'), ('53', 'user_center', 'grant_add_permission', 'lang_add_permission', '', '1'), ('54', 'user_center', 'grant_edit_permission', 'lang_edit_permission', '', '1'), ('55', 'user_center', 'grant_delete_permission', 'lang_delete_permission', '', '1'), ('56', 'user_center', 'grant_grant_view_permission', 'lang_view_permission', '', '1'), ('57', 'user_center', 'grant_grant_add_permission', 'lang_add_permission', '', '1'), ('58', 'user_center', 'grant_grant_edit_permission', 'lang_edit_permission', '', '1'), ('59', 'user_center', 'grant_grant_delete_permission', 'lang_delete_permission', '', '1'), ('90', 'user_center', 'view_user_list', '查看用户列表', '', '0'), ('91', 'user_center', 'grant_view_user_list', '查看用户列表', '', '1'), ('92', 'user_center', 'grant_grant_view_user_list', '查看用户列表', '', '1'), ('93', 'user_center', 'view_role_list', '查看角色管理', '', '0'), ('94', 'user_center', 'grant_view_role_list', '查看角色管理', '', '1'), ('95', 'user_center', 'grant_grant_view_role_list', '查看角色管理', '', '1'), ('96', 'user_center', 'view_block_user_list', '查看被禁用户', '', '0'), ('97', 'user_center', 'grant_view_block_user_list', '查看被禁用户', '', '1'), ('98', 'user_center', 'grant_grant_view_block_user_list', '查看被禁用户', '', '1'), ('99', 'user_center', 'view_complaint_user_list', '查看被投诉用户列表', '', '0'), ('100', 'user_center', 'grant_view_complaint_user_list', '查看被投诉用户列表', '', '1'), ('101', 'user_center', 'grant_grant_view_complaint_user_list', '查看被投诉用户列表', '', '1'), ('102', 'user_center', 'view_application_list', '查看应用管理列表', '', '0'), ('103', 'user_center', 'grant_view_application_list', '查看应用管理列表', '', '1'), ('104', 'user_center', 'grant_grant_view_application_list', '查看应用管理列表', '', '1'), ('105', 'user_center', 'view_lang_project_list', '查看语言包项目列表', '', '0'), ('106', 'user_center', 'grant_view_lang_project_list', '查看语言包项目列表', '', '1'), ('107', 'user_center', 'grant_grant_view_lang_project_list', '查看语言包项目列表', '', '1'), ('108', 'user_center', 'add_lang_project', '添加语言包项目', '', '0'), ('109', 'user_center', 'grant_add_lang_project', '添加语言包项目', '', '1'), ('110', 'user_center', 'grant_grant_add_lang_project', '添加语言包项目', '', '1'), ('111', 'user_center', 'edit_lang_project', '修改语言包项目信息', '', '0'), ('112', 'user_center', 'grant_edit_lang_project', '修改语言包项目信息', '', '1'), ('113', 'user_center', 'grant_grant_edit_lang_project', '修改语言包项目信息', '', '1'), ('114', 'user_center', 'delete_lang_project', '删除语言包项目', '', '0'), ('115', 'user_center', 'grant_delete_lang_project', '删除语言包项目', '', '1'), ('116', 'user_center', 'grant_grant_delete_lang_project', '删除语言包项目', '', '1'), ('117', 'user_center', 'add_menu', '添加菜单', '', '0'), ('118', 'user_center', 'grant_add_menu', '添加菜单', '', '1'), ('119', 'user_center', 'grant_grant_add_menu', '添加菜单', '', '1'), ('120', 'user_center', 'edit_menu', '修改菜单信息', '', '0'), ('121', 'user_center', 'grant_edit_menu', '修改菜单信息', '', '1'), ('122', 'user_center', 'grant_grant_edit_menu', '修改菜单信息', '', '1'), ('123', 'user_center', 'delete_menu', '删除菜单', '', '0'), ('124', 'user_center', 'grant_delete_menu', '删除菜单', '', '1'), ('125', 'user_center', 'grant_grant_delete_menu', '删除菜单', '', '1'), ('126', 'user_center', 'view_feedback', '查看用户反馈', '', '0'), ('127', 'user_center', 'grant_view_feedback', '查看用户反馈', '', '1'), ('128', 'user_center', 'grant_grant_view_feedback', '查看用户反馈', '', '1'), ('129', 'user_center', 'deal_with_feedback', '处理用户反馈', '', '0'), ('130', 'user_center', 'grant_deal_with_feedback', '处理用户反馈', '', '1'), ('131', 'user_center', 'grant_grant_deal_with_feedback', '处理用户反馈', '', '1'), ('132', 'user_center', 'view_app_sceret', '查看应用密钥', '', '0'), ('133', 'user_center', 'grant_view_app_sceret', '查看应用密钥', '', '1'), ('134', 'user_center', 'grant_grant_view_app_sceret', '查看应用密钥', '', '1'), ('135', 'user_center', 'refresh_app_sceret', '重新生成应用密钥', '', '0'), ('136', 'user_center', 'grant_refresh_app_sceret', '重新生成应用密钥', '', '1'), ('137', 'user_center', 'grant_grant_refresh_app_sceret', '重新生成应用密钥', '', '1'), ('138', 'user_center', 'add_application', '添加应用', '', '0'), ('139', 'user_center', 'grant_add_application', '添加应用', '', '1'), ('140', 'user_center', 'grant_grant_add_application', '添加应用', '', '1'), ('141', 'user_center', 'delete_application', '删除应用', '', '0'), ('142', 'user_center', 'grant_delete_application', '删除应用', '', '1'), ('143', 'user_center', 'grant_grant_delete_application', '删除应用', '', '1'), ('144', 'user_center', 'edit_application', '修改应用信息', '', '0'), ('145', 'user_center', 'grant_edit_application', '修改应用信息', '', '1'), ('146', 'user_center', 'grant_grant_edit_application', '修改应用信息', '', '1'), ('147', 'user_center', 'view_app_permission', '查看应用权限', '', '0'), ('148', 'user_center', 'grant_view_app_permission', '查看应用权限', '', '1'), ('149', 'user_center', 'grant_grant_view_app_permission', '查看应用权限', '', '1'), ('150', 'user_center', 'add_app_permission', '添加应用权限', '', '0'), ('151', 'user_center', 'grant_add_app_permission', '添加应用权限', '', '1'), ('152', 'user_center', 'grant_grant_add_app_permission', '添加应用权限', '', '1'), ('153', 'user_center', 'delete_app_permission', '删除应用权限', '', '0'), ('154', 'user_center', 'grant_delete_app_permission', '删除应用权限', '', '1'), ('155', 'user_center', 'grant_grant_delete_app_permission', '删除应用权限', '', '1'), ('156', 'user_center', 'edit_app_permission', '编辑应用权限', '', '0'), ('157', 'user_center', 'grant_edit_app_permission', '编辑应用权限', '', '1'), ('158', 'user_center', 'grant_grant_edit_app_permission', '编辑应用权限', '', '1'), ('159', 'user_center', 'deal_with_complaint', '处理投诉用户的信息', '', '0'), ('160', 'user_center', 'grant_deal_with_complaint', '处理投诉用户的信息', '', '1'), ('161', 'user_center', 'grant_grant_deal_with_complaint', '处理投诉用户的信息', '', '1'), ('162', 'user_center', 'edit_role', '修改角色信息', '', '0'), ('163', 'user_center', 'grant_edit_role', '修改角色信息', '', '1'), ('164', 'user_center', 'grant_grant_edit_role', '修改角色信息', '', '1'), ('165', 'user_center', 'add_role', '添加角色', '', '0'), ('166', 'user_center', 'grant_add_role', '添加角色', '', '1'), ('167', 'user_center', 'grant_grant_add_role', '添加角色', '', '1'), ('168', 'user_center', 'delete_role', '删除角色', '', '0'), ('169', 'user_center', 'grant_delete_role', '删除角色', '', '1'), ('170', 'user_center', 'grant_grant_delete_role', '删除角色', '', '1'), ('171', 'user_center', 'view_role_permission', '查看角色的权限', '', '0'), ('172', 'user_center', 'grant_view_role_permission', '查看角色的权限', '', '1'), ('173', 'user_center', 'grant_grant_view_role_permission', '查看角色的权限', '', '1'), ('174', 'user_center', 'edit_role_permission', '修改角色的权限', '', '0'), ('175', 'user_center', 'grant_edit_role_permission', '修改角色的权限', '', '1'), ('176', 'user_center', 'grant_grant_edit_role_permission', '修改角色的权限', '', '1'), ('177', 'user_center', 'edit_user_status', '修改用户状态', '', '0'), ('178', 'user_center', 'grant_edit_user_status', '修改用户状态', '', '1'), ('179', 'user_center', 'grant_grant_edit_user_status', '修改用户状态', '', '1'), ('180', 'user_center', 'view_user_detail', '查看用户详细信息', '', '0'), ('181', 'user_center', 'grant_view_user_detail', '查看用户详细信息', '', '1'), ('182', 'user_center', 'grant_grant_view_user_detail', '查看用户详细信息', '', '1'), ('183', 'user_center', 'view_user_role', '查看用户的角色', '', '0'), ('184', 'user_center', 'grant_view_user_role', '查看用户的角色', '', '1'), ('185', 'user_center', 'grant_grant_view_user_role', '查看用户的角色', '', '1'), ('186', 'user_center', 'view_user_permission', '查看用户的权限', '', '0'), ('187', 'user_center', 'grant_view_user_permission', '查看用户的权限', '', '1'), ('188', 'user_center', 'grant_grant_view_user_permission', '查看用户的权限', '', '1'), ('189', 'user_center', 'edit_user_permission', '修改用户的权限', '', '0'), ('190', 'user_center', 'grant_edit_user_permission', '修改用户的权限', '', '1'), ('191', 'user_center', 'grant_grant_edit_user_permission', '修改用户的权限', '', '1'), ('192', 'user_center', 'edit_user_role', '修改用户的角色', '', '0'), ('193', 'user_center', 'grant_edit_user_role', '修改用户的角色', '', '1'), ('194', 'user_center', 'grant_grant_edit_user_role', '修改用户的角色', '', '1'), ('195', 'user_center', 'reset_user_password', '重置用户密码', '', '0'), ('196', 'user_center', 'grant_reset_user_password', '重置用户密码', '', '1'), ('197', 'user_center', 'grant_grant_reset_user_password', '重置用户密码', '', '1'), ('198', 'user_center', 'view_customize_menu', '查看自定义菜单', '', '0'), ('199', 'user_center', 'grant_view_customize_menu', '查看自定义菜单', '', '1'), ('200', 'user_center', 'grant_grant_view_customize_menu', '查看自定义菜单', '', '1'), ('201', 'user_center', 'view_project_lang_list', '查看项目的语言包', '', '0'), ('202', 'user_center', 'grant_view_project_lang_list', '查看项目的语言包', '', '1'), ('203', 'user_center', 'grant_grant_view_project_lang_list', '查看项目的语言包', '', '1'), ('204', 'user_center', 'add_project_lang_key', '添加项目的语言键', '', '0'), ('205', 'user_center', 'grant_add_project_lang_key', '添加项目的语言键', '', '1'), ('206', 'user_center', 'grant_grant_add_project_lang_key', '添加项目的语言键', '', '1'), ('207', 'user_center', 'edit_project_lang', '修改项目的翻译', '', '0'), ('208', 'user_center', 'grant_edit_project_lang', '修改项目的翻译', '', '1'), ('209', 'user_center', 'grant_grant_edit_project_lang', '修改项目的翻译', '', '1'), ('210', 'user_center', 'delete_project_lang_key', '删除项目的语言键', '', '0'), ('211', 'user_center', 'grant_delete_project_lang_key', '删除项目的语言键', '', '1'), ('212', 'user_center', 'grant_grant_delete_project_lang_key', '删除项目的语言键', '', '1'), ('213', 'user_center', 'pull_lang_from_php_file', '从PHP文件中拉取语言包', '', '0'), ('214', 'user_center', 'grant_pull_lang_from_php_file', '从PHP文件中拉取语言包', '', '1'), ('215', 'user_center', 'grant_grant_pull_lang_from_php_file', '从PHP文件中拉取语言包', '', '1'), ('216', 'user_center', 'pull_lang_from_js_file', '从JS文件中拉取语言包', '', '0'), ('217', 'user_center', 'grant_pull_lang_from_js_file', '从JS文件中拉取语言包', '', '1'), ('218', 'user_center', 'grant_grant_pull_lang_from_js_file', '从JS文件中拉取语言包', '', '1'), ('219', 'user_center', 'write_lang_to_js_file', '将语言包写入JS文件', '', '0'), ('220', 'user_center', 'grant_write_lang_to_js_file', '将语言包写入JS文件', '', '1'), ('221', 'user_center', 'grant_grant_write_lang_to_js_file', '将语言包写入JS文件', '', '1'), ('222', 'user_center', 'write_lang_to_php_file', '将语言包写入PHP文件', '', '0'), ('223', 'user_center', 'grant_write_lang_to_php_file', '将语言包写入PHP文件', '', '1'), ('224', 'user_center', 'grant_grant_write_lang_to_php_file', '将语言包写入PHP文件', '', '1');
COMMIT;

-- ----------------------------
--  Table structure for `role`
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(40) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `role_permission`
-- ----------------------------
DROP TABLE IF EXISTS `role_permission`;
CREATE TABLE `role_permission` (
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `sms_record`
-- ----------------------------
DROP TABLE IF EXISTS `sms_record`;
CREATE TABLE `sms_record` (
  `sms_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `area_code` smallint(4) unsigned NOT NULL COMMENT '手机区号',
  `mobile` bigint(20) NOT NULL,
  `plat` varchar(20) NOT NULL DEFAULT '' COMMENT '发短信的平台名称',
  `client_ip` varchar(20) DEFAULT '',
  `vk` varchar(32) DEFAULT '' COMMENT '存在客户端的访客唯一标识',
  `refuse_reason` varchar(2000) DEFAULT '' COMMENT '被拒绝原因',
  `mark` varchar(1000) DEFAULT '' COMMENT '其它的备注信息',
  `is_send` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否发送成功，0未发送，1已发送',
  `is_used` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '此验证码是否已使用？0未使用，1已使用',
  `ctime` int(10) unsigned NOT NULL COMMENT '发送时间',
  `mtime` int(10) unsigned NOT NULL COMMENT '最后修改时间，即使用时间',
  PRIMARY KEY (`sms_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `system`
-- ----------------------------
DROP TABLE IF EXISTS `system`;
CREATE TABLE `system` (
  `sys_id` varchar(20) NOT NULL,
  `sys_name` varchar(30) NOT NULL,
  `index_url` varchar(200) DEFAULT NULL,
  `login_api` varchar(200) DEFAULT NULL,
  `sys_key` char(32) DEFAULT NULL,
  `sys_ip` varchar(2000) DEFAULT NULL,
  `ctime` int(10) DEFAULT NULL,
  `usability` tinyint(1) NOT NULL DEFAULT '1',
  `is_fixed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sys_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `try_to_sign_in`
-- ----------------------------
DROP TABLE IF EXISTS `try_to_sign_in`;
CREATE TABLE `try_to_sign_in` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('female','male') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'female',
  `birthday` date DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态，0待注册，1注册成功',
  `avatar` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '用户头像',
  `country` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `province` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `city` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `openid` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '最后更新时间戳',
  `access_token` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `expires_at` bigint(12) NOT NULL DEFAULT '0',
  `refresh_token` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `identity_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '渠道类型，如：微信公众号WX，微信开放平台WXOP，QQ',
  `sid` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户的sid',
  `scan_sid` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '扫码的sessionid',
  `ip` varchar(20) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '用户IP地址',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建的时间戳',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT COMMENT='用户注册前第三方的授权信息';

-- ----------------------------
--  Table structure for `user`
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('female','male') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'female',
  `password` char(32) CHARACTER SET utf8 DEFAULT NULL,
  `salt` char(13) CHARACTER SET utf8 DEFAULT NULL COMMENT '密码加盐',
  `birthday` date DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '用户状态，0被锁，1正常',
  `avatar` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '用户头像',
  `country` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `province` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `city` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `last_active` int(10) NOT NULL DEFAULT '0' COMMENT '最后活跃时间戳',
  `ctime` int(10) NOT NULL DEFAULT '0' COMMENT '创建的时间戳',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `idx_nickname` (`nickname`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT COMMENT='用户的基本资料';

-- ----------------------------
--  Records of `user` Admin默认密码是：YiluPHP@2019
-- ----------------------------
BEGIN;
INSERT INTO `user` VALUES ('1', 'Admin', 'male', '28161c63e4b69043b3f3a285a6f17e4f', '5e0075440f8ec', '2001-10-15', '1', 'https://yiluphp.oss-cn-shenzhen.aliyuncs.com/avatar/2019/1208/15/300x300WxHca2d848d018276f4dedbc131a0752af0.png', 'country_china', '广东省', '深圳市', '1576984101', '1568032612');
COMMIT;

-- ----------------------------
--  Table structure for `user_attribute`
-- ----------------------------
DROP TABLE IF EXISTS `user_attribute`;
CREATE TABLE `user_attribute` (
  `uid` bigint(20) NOT NULL,
  `key` varchar(20) NOT NULL,
  `value` varchar(100) DEFAULT NULL,
  `mtime` int(10) NOT NULL DEFAULT '0',
  `ctime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='更多用户已经设置的属性值';

-- ----------------------------
--  Table structure for `user_attribute_define`
-- ----------------------------
DROP TABLE IF EXISTS `user_attribute_define`;
CREATE TABLE `user_attribute_define` (
  `key` varchar(20) NOT NULL COMMENT '字段的键名，不能与users表中的字段冲突',
  `default_value` varchar(100) DEFAULT '' COMMENT '默认值',
  `description` varchar(30) NOT NULL COMMENT '字段描述，一般作为显示的标题',
  `ctime` int(10) NOT NULL,
  PRIMARY KEY (`key`,`description`,`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='定义用户的更多扩展属性';

-- ----------------------------
--  Table structure for `user_complaint`
-- ----------------------------
DROP TABLE IF EXISTS `user_complaint`;
CREATE TABLE `user_complaint` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `respondent_uid` bigint(20) unsigned NOT NULL COMMENT '被投诉人',
  `complaint_uid` bigint(20) unsigned NOT NULL COMMENT '投诉人',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '投诉状态：0新投诉、1正在处理、2已处理',
  `remark` text COMMENT '管理员备注',
  `ctime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='用户投诉用户的表';

-- ----------------------------
--  Table structure for `user_feedback`
-- ----------------------------
DROP TABLE IF EXISTS `user_feedback`;
CREATE TABLE `user_feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL COMMENT '反馈人用户ID',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态：0新反馈、2正在处理、1已处理',
  `remark` text COMMENT '管理员备注',
  `ctime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='用户反馈表';

-- ----------------------------
--  Table structure for `user_group`
-- ----------------------------
DROP TABLE IF EXISTS `user_group`;
CREATE TABLE `user_group` (
  `uid` bigint(20) NOT NULL,
  `group_id` int(10) NOT NULL,
  PRIMARY KEY (`uid`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Table structure for `user_identity`
-- ----------------------------
DROP TABLE IF EXISTS `user_identity`;
CREATE TABLE `user_identity` (
  `uid` bigint(20) NOT NULL,
  `type` char(6) NOT NULL COMMENT '身份类型，如：INNER表示内部账号(包括邮箱、用户名、手机号)，微信公众号WX，QQ，ALIPAY',
  `identity` varchar(100) NOT NULL COMMENT '登录名或第三方的唯一OPENID',
  `access_token` varchar(1024) NOT NULL DEFAULT '',
  `expires_at` bigint(12) NOT NULL DEFAULT '0',
  `refresh_token` varchar(128) NOT NULL DEFAULT '',
  `ctime` int(10) NOT NULL,
  PRIMARY KEY (`type`,`identity`),
  UNIQUE KEY `userIdentity` (`type`,`identity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='登录名或第三方用户的标识';

-- ----------------------------
--  Records of `user_identity`
-- ----------------------------
BEGIN;
INSERT INTO `user_identity` VALUES ('1', 'INNER', 'admin', '', '0', '', '1570364470');
COMMIT;

-- ----------------------------
--  Table structure for `user_permission`
-- ----------------------------
DROP TABLE IF EXISTS `user_permission`;
CREATE TABLE `user_permission` (
  `uid` bigint(20) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`uid`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
--  Records of `user_permission`
-- ----------------------------
BEGIN;
INSERT INTO `user_permission` VALUES ('1', '60'), ('1', '61'), ('1', '62'), ('1', '63'), ('1', '64'), ('1', '65'), ('1', '66'), ('1', '67'), ('1', '68'), ('1', '69'), ('1', '70'), ('1', '71'), ('1', '72'), ('1', '73'), ('1', '74'), ('1', '75'), ('1', '76'), ('1', '77'), ('1', '78'), ('1', '79'), ('1', '80'), ('1', '81'), ('1', '82'), ('1', '83'), ('1', '87'), ('1', '88'), ('1', '89'), ('1', '90'), ('1', '91'), ('1', '92'), ('1', '93'), ('1', '94'), ('1', '95'), ('1', '96'), ('1', '97'), ('1', '98'), ('1', '99'), ('1', '100'), ('1', '101'), ('1', '102'), ('1', '103'), ('1', '104'), ('1', '105'), ('1', '106'), ('1', '107'), ('1', '108'), ('1', '109'), ('1', '110'), ('1', '111'), ('1', '112'), ('1', '113'), ('1', '114'), ('1', '115'), ('1', '116'), ('1', '117'), ('1', '118'), ('1', '119'), ('1', '120'), ('1', '121'), ('1', '122'), ('1', '123'), ('1', '124'), ('1', '125'), ('1', '126'), ('1', '127'), ('1', '128'), ('1', '129'), ('1', '130'), ('1', '131'), ('1', '132'), ('1', '133'), ('1', '134'), ('1', '135'), ('1', '136'), ('1', '137'), ('1', '138'), ('1', '139'), ('1', '140'), ('1', '141'), ('1', '142'), ('1', '143'), ('1', '144'), ('1', '145'), ('1', '146'), ('1', '147'), ('1', '148'), ('1', '149'), ('1', '150'), ('1', '151'), ('1', '152'), ('1', '153'), ('1', '154'), ('1', '155'), ('1', '156'), ('1', '157'), ('1', '158'), ('1', '159'), ('1', '160'), ('1', '161'), ('1', '162'), ('1', '163'), ('1', '164'), ('1', '165'), ('1', '166'), ('1', '167'), ('1', '168'), ('1', '169'), ('1', '170'), ('1', '171'), ('1', '172'), ('1', '173'), ('1', '174'), ('1', '175'), ('1', '176'), ('1', '177'), ('1', '178'), ('1', '179'), ('1', '180'), ('1', '181'), ('1', '182'), ('1', '183'), ('1', '184'), ('1', '185'), ('1', '186'), ('1', '187'), ('1', '188'), ('1', '189'), ('1', '190'), ('1', '191'), ('1', '192'), ('1', '193'), ('1', '194'), ('1', '195'), ('1', '196'), ('1', '197'), ('1', '198'), ('1', '199'), ('1', '200'), ('1', '201'), ('1', '202'), ('1', '203'), ('1', '204'), ('1', '205'), ('1', '206'), ('1', '207'), ('1', '208'), ('1', '209'), ('1', '210'), ('1', '211'), ('1', '212'), ('1', '213'), ('1', '214'), ('1', '215'), ('1', '216'), ('1', '217'), ('1', '218'), ('1', '219'), ('1', '220'), ('1', '221'), ('1', '222'), ('1', '223'), ('1', '224'), ('1', '225'), ('1', '226'), ('1', '227'), ('1', '228'), ('1', '229'), ('1', '230'), ('1', '231'), ('1', '232'), ('1', '233'), ('1', '234'), ('1', '235'), ('1', '236');
COMMIT;

-- ----------------------------
--  Table structure for `user_role`
-- ----------------------------
DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role` (
  `uid` bigint(20) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`uid`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

SET FOREIGN_KEY_CHECKS = 1;
