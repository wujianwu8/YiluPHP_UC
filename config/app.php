<?php
/*
 * 用户的配置文件
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 09:22
 */

$origin = isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:'';
$allow_origin = ['http://www.ylphp.com','http://www.yiluphp.com','https://www.yiluphp.com'];
if (in_array($origin, $allow_origin)) {
    //制定允许其他域名访问 header("Access-Control-Allow-Origin://www.yiluphp.com");
    header('Access-Control-Allow-Origin:'.$origin);
    //允许请求方式 header("Access-Control-Allow-Methods: POST,GET,PUT,OPTIONS,DELETE");
    header('Access-Control-Allow-Methods:*');
    //请求头
    header('Access-Control-Allow-Headers:*');
    // 响应头设置
    header('Access-Control-Allow-Credentials:true'); //是否可以携带cookie
}

require APP_PATH.'/vendor/autoload.php';

date_default_timezone_set('Asia/Shanghai');

define('REDIS_KEY_MOBILE_VERIFY_CODE', 'REDIS_KEY_MOBILE_VERIFY_CODE_'); //手机验证码的缓存键前缀，后面加“手机号码和session id拼接后的MD5值”
define('REDIS_KEY_EMAIL_VERIFY_CODE', 'REDIS_KEY_EMAIL_VERIFY_CODE_'); //邮箱验证码的缓存键前缀，后面加“邮箱和session id拼接后的MD5值”
define('REDIS_KEY_SEND_VERIFY_CODE_ON_MOBILE', 'REDIS_KEY_SEND_VERIFY_CODE_ON_MOBILE_'); //手机号发送验证码之后，缓存记录标识30秒
define('REDIS_KEY_SEND_VERIFY_CODE_ON_EMAIL', 'REDIS_KEY_SEND_VERIFY_CODE_ON_EMAIL_'); //邮箱发送验证码之后，缓存记录标识30秒
define('REDIS_KEY_SEND_VERIFY_CODE_ON_CLIENT', 'REDIS_KEY_SEND_VERIFY_CODE_ON_CLIENT_'); //客户端发送验证码之后，缓存记录标识30秒
define('REDIS_KEY_SEND_SMS_CODE_ON_IP_5SEC', 'REDIS_KEY_SEND_SMS_CODE_ON_IP_5SEC_'); //同一IP发送短信验证码5秒限制，后面拼接md5后的IP
define('REDIS_KEY_SEND_SMS_CODE_ON_IP_DAY', 'REDIS_KEY_SEND_SMS_CODE_ON_IP_DAY_'); //同一IP发送短信验证码24小时限制，后面拼接md5后的IP
define('REDIS_KEY_PHONE_SMS_IN_TEN_MIN', 'REDIS_KEY_PHONE_SMS_IN_TEN_MIN_'); //HASH存储10分钟内给某个手机号发送验证码的短信平台
define('REDIS_KEY_WEIXIN_QR_LOGIN_CODE', 'REDIS_KEY_WEIXIN_QR_LOGIN_CODE_'); //微信二维码登录时，存储用户的session id，用于登录后对应客户端用户
define('REDIS_KEY_ALL_NICKNAME', 'REDIS_KEY_ALL_NICKNAME'); //HASH存储所有已经使用和被查询为可用的昵称
define('REDIS_KEY_NEW_NICKNAME', 'REDIS_KEY_NEW_NICKNAME'); //HASH存储所有被查询为可用的\但未被定时任务验证的昵称
define('REDIS_KEY_ALL_MENUS', 'REDIS_KEY_ALL_MENUS'); //缓存所有的菜单
/*
 * HASH存储所有已经注册的登录身份及其ID
 * 如果分表则分10个REDIS库存储
*/
define('REDIS_KEY_ALL_IDENTITY', 'REDIS_KEY_ALL_IDENTITY');
define('REDIS_KEY_MOBILE_UID', 'REDIS_KEY_MOBILE_UID');

define('REDIS_KEY_LOGIN_USER_INFO_BY_VK', 'REDIS_KEY_LOGIN_USER_INFO:BY_VK:');   //登录用户的信息,后面接cookie vk 的值
define('REDIS_KEY_LOGIN_USER_INFO_BY_UID', 'REDIS_KEY_LOGIN_USER_INFO:BY_UID:');   //登录用户的信息,后面接UID
define('REDIS_KEY_USER_LOGIN_TLT', 'REDIS_KEY_USER_LOGIN_TLT_');   //临时登录令牌的信息,后面接TLT
define('REDIS_KEY_SEARCH_USER_RESULT', 'REDIS_KEY_SEARCH_USER_RESULT_');    //缓存搜索到的全部用户ID
define('REDIS_KEY_QQ_CALLBACK', 'REDIS_KEY_QQ_CALLBACK_');  //QQ授权登录时，记录是否已经关闭小窗口
define('REDIS_KEY_USER_PERMISSION', 'REDIS_KEY_USER_PERMISSION_');  //缓存用户拥有的所有权限，存储app_id:permission_key格式的
define('REDIS_KEY_USER_PERMISSION_IDS', 'REDIS_KEY_USER_PERMISSION_IDS_');  //缓存用户拥有的所有权限的ID
define('REDIS_KEY_WX_MINI_SESSION_KEY', 'REDIS_KEY_WX_MINI_SK_');  //缓存从微信小程序服务器获取到的openid和session_key
define('REDIS_KEY_TEMP_WX_OPENID', 'REDIS_KEY_TEMP_WX_OPENID_');  //缓存当前获取到的微信OPENID，后面接md5后的vk值
define('REDIS_KEY_UUID_LOCK', 'REDIS_KEY_UUID_LOCK');  //新增UUID时的锁
define('REDIS_KEY_UUID_LIST', 'REDIS_KEY_UUID_LIST');  //当前可用的所有UUID
define('REDIS_KEY_HASH_LAST_WRITE_LANG_FILE', 'REDIS_KEY_HASH_LAST_WRITE_LANG_FILE');  //记录最后一次写入语言文件的时间（HASH格式）

define('TIME_10_YEAR', 315360000); //10年的秒数
define('TIME_5_YEAR', 157680000); //5年的秒数
define('TIME_2_YEAR', 63072000); //2年的秒数
define('TIME_1_YEAR', 31536000); //1年的秒数
define('TIME_60_DAY', 5184000); //60天的秒数
define('TIME_30_DAY', 2592000); //30天的秒数
define('TIME_DAY', 86400); //24小时的秒数
define('TIME_30_MIN', 1800); //30分钟的秒数
define('TIME_10_MIN', 600); //10分钟的秒数
define('TIME_MIN', 60); //1分钟的秒数
define('TIME_30_SEC', 30); //30秒

define('CODE_ATTACKED_BY_CSRF', 30001); //可能遭受CSRF攻击
define('CODE_NOT_CONFIG_SMS_PLAT', 30002);	//未配置短信发送所需要信息
define('CODE_EMAIL_PLAT_CONFIG_ERROR', 30003);	//配置邮件发送平台的信息错误
define('CODE_INVALID', 601); //失效
define('CODE_FAIL_TO_GENERATE_UID', 602); //生成用户ID失败
define('CODE_USER_NOT_LOGIN', -1);	//用户未登录的错误码
define('CODE_WX_MINI_DECRYPT_ILLEGAL_AES_KEY', 41001);	//微信小程序解密数据：sessionKey（encodingAesKey） 非法
define('CODE_WX_MINI_DECRYPT_ILLEGAL_IV', 41002);	//微信小程序解密数据：aes 解密失败
define('CODE_WX_MINI_DECRYPT_ILLEGAL_BUFFER', 41003);	//微信小程序解密数据：解密数据失败
define('CODE_WX_MINI_DECRYPT_ILLEGAL_APP_ID', 41004);	//微信小程序解密数据：解密后的appid不正确

/*
 * 全局配置文件
 */
$config = [
    /*
     * 在这里设置需要重写的路由
     */
    'rewrite_route' => [
        '/menus/edit/{id}' => '/menus/edit/id/{id}',
        '/user/detail/{uid}' => '/user/detail/uid/{uid}',
        '/user/forbidden' => '/user/list/status/0',
        '/user/grant_permission/{uid}' => '/user/grant_permission/uid/{uid}',
        '/user/grant_role/{uid}' => '/user/grant_role/uid/{uid}',
        '/complaint/detail/{id}' => '/complaint/detail/id/{id}',
        '/feedback/detail/{id}' => '/feedback/detail/id/{id}',
        '/application/edit/{app_id}' => '/application/edit/app_id/{app_id}',
        '/application/permission_list/{app_id}' => '/application/permission_list/app_id/{app_id}',
        '/application/add_permission/{app_id}' => '/application/add_permission/app_id/{app_id}',
        '/application/edit_permission/{permission_id}' => '/application/edit_permission/permission_id/{permission_id}',
        '/application/permission_users/{permission_id}' => '/application/permission_users/permission_id/{permission_id}',
        '/role/edit/{role_id}' => '/role/edit/role_id/{role_id}',
        '/role/grant_permission/{role_id}' => '/role/grant_permission/role_id/{role_id}',
        '/role/users/{role_id}' => '/role/users/role_id/{role_id}',
        '/language/edit_project/{project_id}' => '/language/edit_project/project_id/{project_id}',
        '/language/table/{project_id}' => '/language/table/project_id/{project_id}',
    ],

    /**
     * 是否支持多语言切换
     **/
    'multi_Lang' => true,

    //用户默认头像
    'default_avatar' => '/img/default_avatar.gif',

    //是否开放注册
    'open_sign_up' => true,

    //如果静态文件存储在其它平台，在此配置静态文件访问地址前缀，然后执行CLI命令给引入的静态文件加上访问前缀
    //运行的命令是：/你的php目录/php /你的项目目录/yilu build_necessary_redis_data
    'static_file_url_prefix' => '', //如：'https://yiluphp.oss-cn-shenzhen.aliyuncs.com/passport'
];

/*
 * 针对不同环境设置不一样的配置配置信息,建议单独一个文件存放
 */
return array_merge($config, require('/data/config/passport.yiluphp.com/config.php'));