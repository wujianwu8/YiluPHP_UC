<?php
/*
 * 用户的配置文件
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/12/30
 * Time: 19:22
 */

$origin = isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:'';
//设置跨域允许的域名
$allow_origin = ['http://www.yiluphp.com','https://www.yiluphp.com'];
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

require $project_root.'/vendor/autoload.php';

date_default_timezone_set('Asia/Shanghai');

define('REDIS_KEY_MOBILE_VERIFY_CODE', 'REDIS_KEY_MOBILE_VERIFY_CODE_'); //手机验证码的缓存键前缀，后面加“手机号码和session id拼接后的MD5值”
define('REDIS_KEY_EMAIL_VERIFY_CODE', 'REDIS_KEY_EMAIL_VERIFY_CODE_'); //邮箱验证码的缓存键前缀，后面加“邮箱和session id拼接后的MD5值”
define('REDIS_KEY_SEND_VERIFY_CODE_ON_MOBILE', 'REDIS_KEY_SEND_VERIFY_CODE_ON_MOBILE_'); //手机号发送验证码之后，缓存记录标识30秒
define('REDIS_KEY_SEND_VERIFY_CODE_ON_EMAIL', 'REDIS_KEY_SEND_VERIFY_CODE_ON_EMAIL_'); //邮箱发送验证码之后，缓存记录标识30秒
define('REDIS_KEY_SEND_VERIFY_CODE_ON_CLIENT', 'REDIS_KEY_SEND_VERIFY_CODE_ON_CLIENT_'); //客户端发送验证码之后，缓存记录标识30秒
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

define('REDIS_KEY_LOGIN_USER_INFO_BY_VK', 'REDIS_KEY_LOGIN_USER_INFO_BY_VK_');   //登录用户的信息,后面接cookie vk 的值
define('REDIS_KEY_LOGIN_USER_INFO_BY_UID', 'REDIS_KEY_LOGIN_USER_INFO_BY_UID_');   //登录用户的信息,后面接UID
define('REDIS_KEY_USER_LOGIN_TLT', 'REDIS_KEY_USER_LOGIN_TLT_');   //临时登录令牌的信息,后面接TLT
define('REDIS_KEY_SEARCH_USER_RESULT', 'REDIS_KEY_SEARCH_USER_RESULT_');    //缓存搜索到的全部用户ID
define('REDIS_KEY_QQ_CALLBACK', 'REDIS_KEY_QQ_CALLBACK_');  //QQ授权登录时，记录是否已经关闭小窗口
define('REDIS_KEY_USER_PERMISSION', 'REDIS_KEY_USER_PERMISSION_');  //缓存用户拥有的所有权限，存储app_id:permission_key格式的

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
define('CODE_NOT_CONFIG_SMS_PLAT', 30002);  //未配置短信发送所需要信息
define('CODE_EMAIL_PLAT_CONFIG_ERROR', 30003);  //配置邮件发送平台的信息错误
define('CODE_INVALID', 601); //失效
define('CODE_FAIL_TO_GENERATE_UID', 602); //生成用户ID失败
define('CODE_USER_NOT_LOGIN', -1);  //用户未登录的错误码

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
    //用户默认头像
    'default_avatar' => '/img/default_avatar.gif',

    /*
     * 文件上传OSS配置
     * 根据你申请的阿里云OSS信息进行修改
     */
    'oss' => [
        'aliyun' => [
            'accessKeyId' => 'LTAI4FsKnMaaccessKeyId88888',
            'accessKeySecret' => 'aEnXeIxUEaccessKeySecret88888',
            'endpoint' => 'http://oss-cn-shenzhen.aliyuncs.com',
            'bucketName' => 'your_bucketName',
            'visit_host' => 'https://yiluphp.oss-cn-shenzhen.aliyuncs.com/',
        ]
    ],
];

$env_config = [

    /**
     * 是否对数据表进行分表分库,true为分表分库,false为不分表分库,默认为false
     * 如果需要分表分库,需要先配置所有分库的Mysql连接,然后确保停止了增加和修改数据,再手工导数据到各分表
     * 分表方式按表中某整数类型的字段的后两位数进行拆分,拆分成100个分表
     * 分表的库连接名称也是在默认的库连接名称(default)后面加下划线加分表的数字后缀,如default_1, default_23
     **/
    'split_table' => true,

    'mysql' => [
            'default' => [
                'dsn'       =>  'mysql:host=127.0.0.1;port=3306;dbname=yiluuc',
                'username'  =>  'root',
                'password'  =>  '123456',
                'charset'   =>  'utf8',
                'option'    =>  [],
            ]
    ],
    'redis' => [
            'default' => [
                'host'      =>  '127.0.0.1',
                'port'      =>  '6379',
            ]
    ],

    /**
     * 是否为调试模式，此参数为空时调试模式，会显示调试信息
     **/
    'debug_mode' => true,

    /**
     * 队列的运行模式，sync为同步运行，asyn为异步运行
     * 如果不设置,默认为异步运行
     * 异步运行时,需要在后台一直运行着相应的队列才能继续,否则队列数据会一直记录在redis中
     * 执行方式: 例如有队列/cli/queue/like_post.php，则执行命令：php [目录路径]queue queue_name=like_post &
     * 后面加&它就会一直在后台运行着
     **/
    'queue_mode' => 'sync',

    /**
     * 系统的根域名，这里涉及到用户的cookie作用域
     **/
    'root_domain' => 'yiluphp.com',

    /**
     * 默认语言设置，如果你的系统使用多语言，在这里可以设置默认的语言
     **/
    'lang' => 'cn',

    /**
     * 默认的controller名称
     **/
    'default_controller' => 'sign/in',

    /**
     * 在这里设置前置helper类，这些类会在执行controller之前执行
     * before_controller的数组中里面可以配置多个helper的类名
     * 用于before_controller类从构造函数__construct()开始执行
     **/
    'before_controller' => ['hook_route_auth'],

    /**
     * 在这里设置后置helper类，这些类会在执行完controller之后执行
     * after_controller的数组中里面可以配置多个helper的类名
     * 用于after_controller类从构造函数__construct()开始执行
     **/
    'after_controller' => [],

    /**
     * 第三方授权登录的相关配置
     **/
    'oauth_plat' => [
        'qq' => [
            'usable' => true,   //true为可登录,false为不可登录
            'app_key' => '101888888',   //appid
            'app_secret' => '88your_app_secret_888',
            'callback' => 'https://passport.yiluphp.com/sign/qq_callback',
            'authorize' => '',
        ],

        //微信公众平台的
        'wechat' => [
            'usable' => true,   //true为可登录,false为不可登录
            'app_key' => 'wx888your_app_key_888',   //appid
            'app_secret' => '88your_app_secret_888',
            'callback' => 'https://passport.yiluphp.com/sign/wechat_callback',
        ],
        //微信开放平台的
        'wechat_open' => [
            'usable' => true,   //true为可登录,false为不可登录
            'app_key' => 'wx888your_app_key_888',   //appid
            'app_secret' => '88your_app_secret_888',
            'callback' => 'https://passport.yiluphp.com/sign/wechat_callback/open/1',
        ],

        'alipay' => [
            'usable' => true,   //true为可登录,false为不可登录
            'app_id' => '20191016666688888',   //appid
            //请填写开发者私钥去头去尾去回车，一行字符串
            'rsa_private_key' => 'MIIEvg...这中间有很长的一段字符串...BBB',
            //请填写开发者公钥，一行字符串
            'rsa_public_key' => 'MIIBI...这中间有很长的一段字符串...ABC',
            //请填写支付宝公钥
            'alipay_rsa_public_key' => 'MIIBI...这中间有很长的一段字符串...ABC',
            'sign_type' => 'RSA2',
            //接口内容加密方式：AES密钥
            'encrypt_key' => 'OpM888your_encrypt_key==',

            'callback' => 'https://passport.yiluphp.com/sign/alipay_callback',
        ],
    ],

    /*
     * 自定义需要显示的错误级别
        1     E_ERROR           致命的运行错误。错误无法恢复，暂停执行脚本。
        2     E_WARNING         运行时警告(非致命性错误)。非致命的运行错误，脚本执行不会停止。
        4     E_PARSE           编译时解析错误。解析错误只由分析器产生。
        8     E_NOTICE          运行时提醒(这些经常是你代码中的bug引起的，也可能是有意的行为造成的。)
        16    E_CORE_ERROR PHP  启动时初始化过程中的致命错误。
        32    E_CORE_WARNING    PHP启动时初始化过程中的警告(非致命性错)。
        64    E_COMPILE_ERROR   编译时致命性错。这就像由Zend脚本引擎生成了一个E_ERROR。
        128   E_COMPILE_WARNING 编译时警告(非致性错)。这就像由Zend脚本引擎生成了E_WARNING警告。
        256   E_USER_ERROR      自定义错误消息。像用PHP函数trigger_error（程序员设置E_ERROR）
        512   E_USER_WARNING    自定义警告消息。像用PHP函数trigger_error（程序员设的E_WARNING警告）
        1024  E_USER_NOTICE     自定义的提醒消息。像由使用PHP函数trigger_error（程序员E_NOTICE集）
        2048  E_STRICT          编码标准化警告。允许PHP建议修改代码以确保最佳的互操作性向前兼容性。
        4096  E_RECOVERABLE_ERROR   开捕致命错误。像E_ERROR，但可以通过用户定义的处理捕获（又见set_error_handler（））
        8191  E_ALL             所有的错误和警告(不包括 E_STRICT) (E_STRICT will be part of E_ALL as of PHP 6.0)
        16384 E_USER_DEPRECATED
        30719 E_ALL
        可用直接使用数字，也可以使用常量的计算公式，例如：
         error_reporting(0);                //禁用错误报告
         error_reporting(E_ERROR | E_WARNING | E_PARSE);//报告运行时错误
         error_reporting(E_ALL);            //报告所有错误
         error_reporting(E_ALL ^ E_NOTICE); //除E_NOTICE报告所有错误，是在php.ini的默认设置
         error_reporting(-1);               //报告所有 PHP 错误
         error_reporting(3);                //不报E_NOTICE
         error_reporting(11);               //报告所有错误
         ini_set('error_reporting', E_ALL); // 和 error_reporting(E_ALL); 一样
         error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);//表示php错误，警告，语法错误，提醒都返错。
     * */
    'error_level' => E_ALL,

    /*
     * 定义会写入文件日志的类型，只有在log_level数组中的类型才会写入日志
     * 可选值：ERROR错误、WARNING警告、DEBUG调试、NOTICE通知、VISIT访问、RESPONSE正常响应、TRACE代码追溯
     * 另外可以任意自定义自己想用的类型，直接写入该数组就行
     * */
    'log_level' => ['ERROR', 'WARNING', 'DEBUG', 'NOTICE', 'VISIT', 'RESPONSE', 'ERROR', 'TRACE'],

    /*
     * 是否使用session，true为使用，false为不使用
     * YiluPHP的session是使用redis存储的，可以实现集群服务器之间共享session
     * */
    'use_session' => true,

    /*
     * 发短信和语音的平台列表
     * 键名需与sms.php的方法名对应
     * */
    'sms' => [
        //阿里云短信推送
        'aliyun' => [
            'access_key_id' => 'your_access_key_id',   //在阿里云申请到的accessKeyId
            'access_key_secret' => 'your_access_key_secret',   //在阿里云申请到的accessKeySecret
            'region_id' => 'cn-shengzhen',   //在阿里云申请到的短信使用区域regionId
            'template_code_zh' => 'SMS_176928888',   //短信模板CODE，中文
            'template_code_en' => 'SMS_176946666',   //短信模板CODE，英文
            'sign_name_zh' => 'YiluPHP',   //短信签名，中文
            'sign_name_en' => 'YiluPHP',   //短信签名，英文
        ],
        //云片短信平台，注释了则不会使用
//        'yun_pian' => [
//            'api_key' => '41eb4325daaaaaafeeeee9a98199c888888888',   //在云片申请到的api key
//        ],
    ],

    /*
     * 当前环境标识，这在区分环境执行不一样的代码时非常有用
     * 比如：local代表开发者自己的电脑，dev代表开发环境，alpha代表测试环境，beta代表预发环境，idc或不设置代表线上（生产）环境
     * 如果在这里没设置，就会去/data/config/env文件中读，如果/data/config/env中也没有则默认为idc
     * */
    'env' => 'dev',

    /*
     * 邮件系统相关配置,目前支持使用PHPmailer组件和阿里云邮件推送产品进行邮件送送
     * 阿里云邮件推送产品按量收费,没有最低消费要求,且每天有200条的免费邮件可以使用
     * */
    'mailer' =>[
        //在此使用可使用的发邮件方式,可配置多个,phpmailer或aliyun
//        'usable' => ['aliyun', 'phpmailer'],
        'usable' => ['aliyun'],
        //是否强制使用phpmailer给QQ邮箱发邮件
        //设置为true则一定使用phpmailer给QQ邮箱发邮件,这样做是因为使用阿里云的邮件推送容易进QQ邮箱的垃圾桶里
        'qq_email_use_phpmailer' => true,
        //使用阿里云邮件推送产品的相关配置
        'aliyun' => [
            'weight' => 1, //使用的权重，0-100的整数，数值越大使用的概率就越大，不设置默认为1
            'access_key_id' => 'your_access_key_id',
            'access_key_secret' => 'your_access_key_secret',
            //用于发送找回密码的邮件的地址
            'from_email' => 'notice@yiluphp.cn',
            //用于发件人名称
            'from_name' => 'YiluPHP邮件通知系统',
        ],
        //使用PHPmailer组件的相关配置
        'phpmailer' => [
            'weight' => 10, //使用的权重，0-100的整数，数值越大使用的概率就越大，不设置默认为1
            //用于发送邮件的地址
            'from_email' => '888888@qq.com',
            //用于发件人名称
            'from_name' => 'YiluPHP邮件通知系统',
            'host' => 'smtp.qq.com',    //指定发邮件的主服务器和备份SMTP服务器
            'mailer_type' => 'smtp',    //邮箱服务器类型:smtp, mail, sendmail, qmail,
            'username' => '888888@qq.com',    //SMTP用户名
            'password' => 'your_smtp_password',   //SMTP密码
            'port' => 465,
            'SMTP_secure' => 'ssl', //启用TLS加密，也接受'ssl'
            'reply_to_email' => '666666@qq.com', //接收回信的邮箱地址
            //设置错误信息的语言,默认为zh_cn
            'language' => 'zh_cn',
            //启用详细调试输出,`0` No output，`1` Commands，`2` Data and commands，`3` As 2 plus connection status，`4` Low-level data output.
            'debug' => 0,
        ],
    ],

    /*
     * 用于RAS解密用的私钥
     * 可以百度一下生成方法,将生成的private_key.pem和public_key.pem文件拷贝到你希望的、可以长期存放的位置
     * 将private_key.pem的内容赋值给ras_private_key参数
     * 将public_key.pem的内容赋值给ras_public_key参数
     * 你可以使用file_get_contents动态获取文件内容，为了减少读磁盘文件的操作，
     * 你也可以把文件的内容拷贝出来，原样粘贴在这两个参数的值
     * */
    'rsa_private_key' => file_get_contents($GLOBALS['project_root'].'document/rsa_private_key.pem'),
    'rsa_public_key' => file_get_contents($GLOBALS['project_root'].'document/rsa_public_key.pem'),

    /*
     * 官网首页，用于头部Logo的链接
     * */
    'website_index' => 'http://www.yiluphp.com',

];

/*
 * 针对不同环境设置不一样的配置配置信息,建议单独一个文件存放
 */
// return array_merge($config, require('/data/config/www.yourhost.com/config.php'));
return array_merge($config, $env_config);