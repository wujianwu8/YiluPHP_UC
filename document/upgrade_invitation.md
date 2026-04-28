# 邀请机制升级说明
# 时间：2026-04-28

本文用于把旧版本 YiluphpUC 升级到包含“邀请机制”的版本。

## 本次升级包含的内容

1. 新增 `invitation_link` 数据表，用于保存不同场景的邀请码。
2. 在 `user` 表增加以下字段：
   - `register_invitation_link_id`
   - `register_inviter_uid`
3. 新增后台菜单：
   - `邀请链接`
4. 新增用户侧菜单：
   - `邀请注册`
5. 新增内部接口：
   - `/internal/find_invitation_link_by_code`
   - `/internal/find_invitation_link_by_uid`
   - `/internal/create_invitation_link`
6. 新增前置 hook：
   - `hook_invitation_capture`

## 升级步骤

### 第一步：更新代码

把最新版本代码覆盖到你的项目目录。

### 第二步：执行升级脚本

在项目根目录执行：

```bash
php yilu upgrade_invitation
```

这个脚本会完成以下事情：
- 创建 `invitation_link` 表
- 给 `user` 主表和 `user_0 ~ user_99` 分表增加邀请注册字段
- 增加邀请相关的固定权限
- 增加邀请相关的系统菜单
- 默认把新增后台权限授予 `uid=1` 的管理员

### 第三步：重建索引和缓存

执行完升级脚本之后，**必须继续执行下面这个脚本**：

```bash
php yilu build_necessary_redis_data
```

如果你跳过这一步，部分菜单缓存、权限缓存或用户相关索引可能不是最新状态。

### 第四步：启用邀请码捕获 hook

打开配置文件，找到 `before_controller` 配置。

示例文件参考：
- `document/config.php`
- 实际运行环境通常是 `config/app.php` 合并外部配置文件后的结果

把下面这个类加入到 `before_controller` 中，并且建议放在 `hook_route_auth` 前面：

```php
'before_controller' => ['hook_csrf', 'hook_invitation_capture', 'hook_route_auth']
```

### 第五步：确认根域配置

邀请码 cookie 是按根域写入的，所以请确认配置中的 `root_domain` 已正确设置。

例如：

```php
'root_domain' => 'example.com'
```

这样所有子域名都可以读取同一个邀请码 cookie。

## 新功能说明

### 1. 邀请码写入 cookie

当用户访问的 URL 中带有：

```text
?invite_code=xxxxxx
```

系统会自动根据邀请码查询邀请链接，并将邀请码写入对应场景的 cookie。

当前注册场景默认写入的 cookie 名为：

```text
invite_register
```

### 2. 注册归因

当用户使用注册链接进入并完成注册后，系统会把邀请人信息写入 `user` 表中的：

- `register_invitation_link_id`
- `register_inviter_uid`

### 3. 用户侧页面

登录后，所有用户都可以在左侧看到：

- `邀请注册`

该页面可以：
- 查看自己的注册链接
- 更换自己的邀请码
- 查看邀请自己注册的人
- 查看自己邀请注册过来的用户

### 4. 后台页面

有权限的管理员可以在左侧看到：

- `邀请链接`

该页面可以：
- 查看所有邀请链接
- 按 uid、scene、邀请码、备注筛选
- 新建邀请链接
- 编辑邀请链接备注和 cookie 有效期
- 复制注册链接

## 注意事项

1. 本次实现只落地了“邀请注册”的归因入库。
2. 其它业务场景的邀请码目前只负责写 cookie，后续由业务系统自行读取并消费。
3. 邀请码默认从 6 位字符开始随机生成。
4. 邀请链接表中的 `cookie_ttl` 默认是 15 天，单位为秒。
5. 用户主动“更换邀请码”时，会直接覆盖原邀请码，不会保留旧码。

## 常见问题

### 开启了分表，是否必须执行升级脚本？

必须执行。因为升级脚本会同时处理 `user` 主表和 `user_0 ~ user_99` 分表字段。

### 只导入新的 SQL 行不行？

不建议。旧系统升级请直接执行：

```bash
php yilu upgrade_invitation
php yilu build_necessary_redis_data
```

### fresh install 是否还需要执行升级脚本？

如果你直接使用最新版本的 `document/YiluphpUC.sql` 初始化数据库，理论上不需要再跑表结构升级；但为了确保菜单、权限和缓存状态一致，仍建议执行一遍：

```bash
php yilu upgrade_invitation
php yilu build_necessary_redis_data
```
