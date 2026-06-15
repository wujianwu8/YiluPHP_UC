# 上传文件入库升级说明
# 时间：2026-06-15

本文用于把旧版本 YiluphpUC 升级到包含“上传文件入库管理”的版本。

## 本次升级包含的内容

1. 调整 `file` 数据表结构：
   - `url` 字段长度由 `varchar(200)` 扩充为 `varchar(512)`，以适配 OSS 等带完整域名的访问地址。
   - `type` 字段由 `enum('avatar')` 改为 `varchar(32) NOT NULL DEFAULT ''`，方便其它子系统使用时自定义类型。
2. 新增模型类 `model/model_file.php`，提供公共的入库方法 `insert_file()`。
3. 两个上传接口都增加 `type` 参数，并把上传成功后的文件信息写入 `file` 表：
   - `POST /uploader/form_image` （表单方式上传图片）
   - `POST /setting/save_avatar` （base64 方式上传头像，控制器为 `controller/uploader/binary_image.php`）

## 升级步骤

### 第一步：更新代码

把最新版本代码覆盖到你的项目目录，至少包含以下文件：

- `model/model_file.php`（新增）
- `controller/uploader/form_image.php`（修改）
- `controller/uploader/binary_image.php`（修改）
- `controller/setting/save_avatar.php`（修改）
- `document/YiluphpUC.sql`（更新了 `file` 表 DDL）

### 第二步：执行 SQL 升级

直接在线上库执行下面的 ALTER 语句即可，无需重建表、无需丢数据：

```sql
ALTER TABLE `file`
    MODIFY `url`  varchar(512) NOT NULL COMMENT '文件访问地址',
    MODIFY `type` varchar(32)  NOT NULL DEFAULT '' COMMENT '文件用途类型，由各子系统自定义，如avatar';
```

如果你的旧库没有 `file` 表（例如更早的版本），请直接使用最新的 `document/YiluphpUC.sql` 中的建表语句创建：

```sql
DROP TABLE IF EXISTS `file`;
CREATE TABLE `file` (
  `file_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(512) NOT NULL COMMENT '文件访问地址',
  `type` varchar(32) NOT NULL DEFAULT '' COMMENT '文件用途类型，由各子系统自定义，如avatar',
  `create_at` int(10) NOT NULL COMMENT '上传时间戳',
  `ip` varchar(20) DEFAULT NULL COMMENT '上传者IP',
  `uid` bigint(20) DEFAULT NULL COMMENT '上传者用户ID',
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='上传文件记录表';
```

## 接口变更说明

### 1. `POST /uploader/form_image`

新增可选请求参数：

| 参数 | 类型 | 必选 | 说明 |
|------|------|------|------|
| type | string | 否 | 文件用途类型，会写入 `file` 表的 `type` 字段，默认空字符串 |

返回结构未变。

### 2. `POST /setting/save_avatar`（`controller/uploader/binary_image.php`）

新增可选请求参数：

| 参数 | 类型 | 必选 | 说明 |
|------|------|------|------|
| type | string | 否 | 文件用途类型，会写入 `file` 表的 `type` 字段，默认空字符串。业务上若不传，建议传 `avatar` 以便后续按类型清理 |

返回结构未变。

## 设计说明

- 入库统一封装在 `model_file::I()->insert_file($url, $type, $uid, $ip)` 中。
- 入库失败**不会**影响接口响应。出于"上传成功就应该返回成功"的考虑，入库失败只会通过 `write_applog('ERROR', ...)` 写日志，避免日志表故障导致用户重复上传。
- 对于头像上传还是表单上传，记录的都是不含域名的**文件的访问路径**，这是为了方便以后删除文件。

## 其它子系统接入建议

在调用方传 `type` 时，请使用一个能反映用途的短字符串，例如：

- `avatar` —— 用户头像
- `feedback` —— 用户反馈附件
- `application_logo` —— 应用图标

后续如果需要清理“无人引用”的文件，可以按 `type` 配合各业务表的引用字段做关联检查。
