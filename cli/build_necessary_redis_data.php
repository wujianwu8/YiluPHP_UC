<?php
/*
 * 创建必须的REDIS缓存
 * OneWayPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/31
 * Time: 21:33
 */
if(!isset($_SERVER['REQUEST_URI'])){
    $the_argv = $argv;
    unset($the_argv[0]);
    $_SERVER['REQUEST_URI'] = 'php '.$argv[0].' "'.implode('" "', $the_argv).'"';
}
$project_root = explode('/cli/', __FILE__);
$project_root = $project_root[0].'/';
include_once($project_root.'public/index.php');

$limit = 1000;
//生成用户身份主表中的用户身份的缓存
$page = 0;
do{
    $data = $app->model_user_identity->paging_select([], $page, $limit, 'uid ASC', 'uid, `type`, `identity`');
    foreach ($data as $item){
        $app->model_user_identity->cache_user_identity($item['type'], $item['identity'], $item['uid']);
    }
    $page++;
}
while($data);

//生成用户信息主表中的用户昵称的缓存
$page = 0;
do{
    $data = $app->model_user->paging_select([], $page, $limit, 'uid ASC', 'uid, `nickname`');
    foreach ($data as $item){
        $app->redis()->hset(REDIS_KEY_ALL_NICKNAME, md5($item['nickname']), 1);
    }
    $page++;
}
while($data);

if (!empty($GLOBALS['config']['split_table'])) {
    for ($i = 0; $i < 100; $i++) {

        //生成用户身份 分 表中的用户身份的缓存
        $page = 0;
        do{
            $data = $app->model_user_identity->paging_select([], $page, $limit, 'uid ASC', 'uid, `type`, `identity`', $i);
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
                $app->redis($sub_redis_name)->hset(REDIS_KEY_ALL_IDENTITY, $key, $item['uid']);
            }
            $page++;
        }
        while($data);

        //生成用户信息 分 表中的用户昵称的缓存
        $page = 0;
        do{
            $data = $app->model_user->paging_select([], $page, $limit, 'uid ASC', 'uid, `nickname`', $i);
            foreach ($data as $item){
                $app->redis()->hset(REDIS_KEY_ALL_NICKNAME, md5($item['nickname']), 1);
            }
            $page++;
        }
        while($data);
    }
}

exit("\r\n完成\r\n\r\n");
