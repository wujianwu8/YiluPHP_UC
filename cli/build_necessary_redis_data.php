<?php
/*
 * 创建必须的REDIS缓存
 * 运行命令：/usr/local/php7.4.16/bin/php /data/web/passport.yiluphp.com/yilu build_necessary_redis_data
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 21:33
 */

$limit = 1000;
//生成用户身份主表中的用户身份的缓存
$page = 0;
do{
    $data = model_user_identity::I()->paging_select([], $page, $limit, 'uid ASC', 'uid, `type`, `identity`');
    foreach ($data as $item){
        model_user_identity::I()->cache_user_identity($item['type'], $item['identity'], $item['uid']);
    }
    $page++;
}
while($data);

//生成用户信息主表中的用户昵称的缓存
$page = 0;
do{
    $data = model_user::I()->paging_select([], $page, $limit, 'uid ASC', 'uid, `nickname`');
    foreach ($data as $item){
        redis_y::I()->hset(REDIS_KEY_ALL_NICKNAME, md5($item['nickname']), 1);
    }
    $page++;
}
while($data);

if (!empty($GLOBALS['config']['split_table'])) {
    for ($i = 0; $i < 100; $i++) {

        //生成用户身份 分 表中的用户身份的缓存
        $page = 0;
        do{
            $data = model_user_identity::I()->paging_select([], $page, $limit, 'uid ASC', 'uid, `type`, `identity`', $i);
            foreach ($data as $item){
                $key = $item['type'].'-'.$item['identity'];
                if (!empty($GLOBALS['config']['split_table'])){
                    $split_num = getOneIntegerByStringASCII($key);
                    $sub_redis_name = 'default_'.$split_num;
                }
                else{
                    $sub_redis_name = 'default';
                }
                $key = md5($key);
                redis_y::I($sub_redis_name)->hset(REDIS_KEY_ALL_IDENTITY, $key, $item['uid']);
            }
            $page++;
        }
        while($data);

        //生成用户信息 分 表中的用户昵称的缓存
        $page = 0;
        do{
            $data = model_user::I()->paging_select([], $page, $limit, 'uid ASC', 'uid, `nickname`', $i);
            foreach ($data as $item){
                redis_y::I()->hset(REDIS_KEY_ALL_NICKNAME, md5($item['nickname']), 1);
            }
            $page++;
        }
        while($data);
    }
}


//生成UUID的缓存
$min_id = 0;
do{
    $where = [
        'status' => 1,
        'uuid' => [
            'symbol' => '>',
            'value' => $min_id,
        ],
    ];
    if($data = model_uuid_stock::I()->paging_select($where, 1, $limit, 'uuid ASC', '`uuid`')){
        $ids = array_column($data,'uuid');
        redis_y::I()->sAddArray(REDIS_KEY_UUID_LIST, $ids);
        $min_id = max($ids);
    }
}
while($data);

exit("\r\n完成\r\n\r\n");
