<?php
/*
 * UUID处理类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2022/03/18
 * Time: 21:21
 */

class logic_uuid
{

    protected static $instance = null;

    /**
     * 获取单例
     */
    public static function I(){
        if (empty(self::$instance)){
            return self::$instance = new static();
        }
        return self::$instance;
    }

	public function __construct()
	{
	}

	public function __destruct()
	{
	}

    /**
     * 添加一批UUID
     * @param $count 需要添加的数量
     * @param false $disuse_old 是否需要弃用老的这批UUID，只有当次$count参数大于1万，且添加前的剩余数量小于5万时才会删除
     * @return bool
     * @throws Exception
     */
    public function batch_insert_uuid($count, $disuse_old=false)
    {
        //加锁
        if (!redis_y::I()->set(REDIS_KEY_UUID_LOCK,1, ['nx', 'ex'=>600])){
            return false;
        }
        redis_y::I()->expire(REDIS_KEY_UUID_LOCK,300);
        //获取当前最大的UUID
        $max_uuid = model_uuid_stock::I()->find_table([],'MAX(uuid) AS id');
        $max_uuid = intval($max_uuid['id']);
        //往数据库添加uuid
        model_uuid_stock::I()->batch_insert_uuid($count);
        //获取添加后的最大的UUID
        $max_uuid2 = model_uuid_stock::I()->find_table([],'MAX(uuid) AS id');
        $max_uuid2 = intval($max_uuid2['id']);
        if ($max_uuid==$max_uuid2){
            redis_y::I()->del(REDIS_KEY_UUID_LOCK);
            return false;
        }

        //如果当前可用的UUID小于5万个，则弃用旧的UUID
        $disuse_ids = false;
        if ($count>10000 && $max_uuid>0 && $disuse_old){
            $zcard = redis_y::I()->zCard(REDIS_KEY_UUID_LIST);
            $disuse_ids = $zcard<50000;
        }

        if ($max_uuid<=0){
            $max_uuid = model_uuid_stock::I()->find_table([],'MIN(uuid) AS id');
            $max_uuid = intval($max_uuid['id']);
            if ($max_uuid==0){
                redis_y::I()->del(REDIS_KEY_UUID_LOCK);
                return false;
            }
            $max_uuid = $max_uuid-1;
        }
        //往Redis库中添加数据
        $ids = [];
        for ($i=$max_uuid+1; $i<=$max_uuid2; $i++) {
            $ids[] = $i;
            if (count($ids)>=2000) {
                redis_y::I()->sAddArray(REDIS_KEY_UUID_LIST, $ids);
                $ids = [];
            }
        }
        if (count($ids)>0) {
            redis_y::I()->sAddArray(REDIS_KEY_UUID_LIST, $ids);
        }
        unset($ids);

        if ($disuse_ids){
            //弃用旧的UUID
            //修改数据库中的状态
            $where = [
                'status' => 1,
                'uuid' => [
                    'symbol' => '<=',
                    'value' => $max_uuid,
                ],
            ];
            model_uuid_stock::I()->change_table($where, ['status'=>0]);
            //删除Redis中的
            $ids = [];
            for ($i=$max_uuid; $i>0; $i--) {
                $ids[] = $i;
                if (count($ids)>=1000) {
                    $params = [REDIS_KEY_UUID_LIST];
                    call_user_func_array([redis_y::I(), 'sRem'], array_merge($params, $ids));
                    $ids = [];
                    if (!redis_y::I()->sIsMember(REDIS_KEY_UUID_LIST, $i-1)){
                        break;
                    }
                }
            }
            if (count($ids)>0) {
                $params = [REDIS_KEY_UUID_LIST];
                call_user_func_array([redis_y::I(), 'sRem'], array_merge($params, $ids));
            }
            unset($ids);
        }
        //解锁
        redis_y::I()->del(REDIS_KEY_UUID_LOCK);
        return true;
    }

    /**
     * 添加一批UUID
     * @param $module 用于哪个内容模块，不超过20个字符
     * @param $count 需要添加的数量
     * @param false $disuse_old 是否需要弃用老的这批UUID，只有当次$count参数大于1万，且添加前的剩余数量小于5万时才会删除
     * @return bool
     * @throws Exception
     */
    public function get_uuid($module, $count=1)
    {
        $module = trim($module);
        $count = intval($count);
        if ($count<1){
            return 0;
        }
        if(!$uuid = redis_y::I()->sRandMember(REDIS_KEY_UUID_LIST, $count)){
            //没有UUID就先添加1万条先用着
            if(!$this->batch_insert_uuid(10000)){
                throw new Exception('获取UUID失败，添加UUID失败了',CODE_SYSTEM_ERR);
            }
            //调用异步任务添加10万个UUID
            add_to_queue('batch_insert_uuid',['count'=>100000, 'disuse_old'=>false]);
            //再次获取UUID
            if(!$uuid = redis_y::I()->sRandMember(REDIS_KEY_UUID_LIST, $count)){
                throw new Exception('获取UUID失败，再次获取UUID失败了',CODE_SYSTEM_ERR);
            }
        }
        //更新数据库中uuid的状态为已使用
        $where = [
            'status' => 1,
            'uuid' => [
                'symbol' => 'IN',
                'value' => $uuid,
            ],
        ];
        if(!model_uuid_stock::I()->change_table($where, ['status'=>2,'module'=>$module])){
            throw new Exception('获取UUID失败，更新数据库uuid状态失败了，$uuid='.json_encode($uuid),CODE_SYSTEM_ERR);
        }

        $params = [REDIS_KEY_UUID_LIST];
        $params = array_merge($params, $uuid);
        if(!$res=call_user_func_array([redis_y::I(), 'sRem'], $params)){
            throw new Exception('获取UUID后，从redis中删除ID失败了，$uuid='.json_encode($uuid),CODE_SYSTEM_ERR);
        }

        if ($count==1){
            return array_shift($uuid);
        }
        return $uuid;
    }
}
