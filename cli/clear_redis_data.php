<?php
/*
 * 清除所有REDIS缓存
 * 运行命令：/usr/local/php7.4.16/bin/php /data/web/passport.yiluphp.com/yilu clear_redis_data
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 21:45
 */

//删除所有已用的Nickname
redis_y::I()->del(REDIS_KEY_ALL_NICKNAME);

//删除所有已用的身份
redis_y::I()->del(REDIS_KEY_ALL_IDENTITY);

exit("\r\n完成\r\n\r\n");