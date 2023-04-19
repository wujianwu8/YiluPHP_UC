<?php
/*
 * 用户模型类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 22:16
 */

class model_user_identity extends model
{
    protected $_table = 'user_identity';
	//拆分表的方式，null表示不拆分，last_two_digits表示根据（如ID）末尾2位数拆分成100个表
	protected $_split_method = 'last_two_digits';
	//用于分表的字段名
	protected $_split_by_field = 'uid';

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
	 * @name 获取用户总数量
	 * @desc
	 * @return integer
	 */
	public function get_user_count()
	{
		return redis_y::I()->hlen(REDIS_KEY_MOBILE_UID);
	}

	/**
	 * @name 根据身份类型查询其用户ID
	 * @desc
	 * @param string $type 身份类型 INNER表示内部账号(包括邮箱、用户名、手机号)，WX表示微信，QQ
	 * @param string $identity 登录身份或第三方的openid
	 * @return integer/null 返回用户ID或NULL,如果返回NULL则表示此登录身份未注册
	 */
	public function find_uid_by_identity($type, $identity)
	{
	    if ($type=='INNER') {
            $identity = strtolower($identity);
        }
		$key = $type.'-'.$identity;
		if (!empty($GLOBALS['config']['split_table'])){
			$split_num = getOneIntegerByStringASCII($key);
			$sub_redis_name = 'default_'.$split_num;
		}
		else{
			$sub_redis_name = 'default';
		}
		$key = md5($key);
		if(redis_y::I($sub_redis_name)->hexists(REDIS_KEY_ALL_IDENTITY, $key)){
			return redis_y::I($sub_redis_name)->hget(REDIS_KEY_ALL_IDENTITY, $key);
		}
		return null;
	}

    /**
     * @name 把用户身份存入缓存
     * @desc 可供查询指定身份是否已经注册
     * @param string $type 身份类型 INNER表示内部账号(包括邮箱、用户名、手机号)，WX表示微信，QQ
     * @param string $identity 登录身份或第三方的openid
     * @param string $uid 用户ID
     * @return integer  如果 field 是哈希表中的一个新建域，并且值设置成功，返回 1 。
     * 					如果哈希表中域 field 已经存在且旧值已被新值覆盖，返回 0 。
     */
    public function cache_user_identity($type, $identity, $uid)
    {
        if ($type=='INNER') {
            $identity = strtolower($identity);
        }
        $key = $type.'-'.$identity;
        if (!empty($GLOBALS['config']['split_table'])){
            $split_num = getOneIntegerByStringASCII($key);
            $sub_redis_name = 'default_'.$split_num;
        }
        else{
            $sub_redis_name = 'default';
        }
        if(preg_match("/^\d+-\d+$/", $identity)){
            redis_y::I()->hset(REDIS_KEY_MOBILE_UID, $identity, $uid);
        }
        $key = md5($key);
        return redis_y::I($sub_redis_name)->hset(REDIS_KEY_ALL_IDENTITY, $key, $uid);
    }

    /**
     * @name 删除缓存中的用户的登录身份
     * @desc 此缓存可供查询指定身份是否已经注册
     * @param string $type 身份类型 INNER表示内部账号(包括邮箱、用户名、手机号)，WX表示微信，QQ
     * @param string $identity 登录身份或第三方的openid
     * @param string $uid 用户ID
     * @return integer  返回删除数量
     */
    public function delete_user_identity_cache($type, $identity, $uid)
    {
        $key = $type.'-'.$identity;
        if (!empty($GLOBALS['config']['split_table'])){
            $split_num = getOneIntegerByStringASCII($key);
            $sub_redis_name = 'default_'.$split_num;
        }
        else{
            $sub_redis_name = 'default';
        }
        if(preg_match("/^\d+-\d+$/", $identity)){
            redis_y::I()->hdel(REDIS_KEY_MOBILE_UID, $identity);
        }
        $key = md5($key);
        return redis_y::I($sub_redis_name)->hdel(REDIS_KEY_ALL_IDENTITY, $key);
    }

    /**
     * @name 根据多个用户ID查询用户的身份信息
     * @desc 分页读取
     * @param array $uids 多个用户ID的数据
     * @param string $fields 需要返回的字段
     * @param string $field_value 用于分表的字段的值
     * @return array 数据列表
     */
    function select_user_identity_by_multi_uids(array $uids, string $fields='*', $field_value=null)
    {
        $table_name = $this->sub_table($field_value);
        $connection = $this->sub_connection($field_value);
        $plist = ':uid_'.implode(',:uid_', array_keys($uids));
        $params = array_combine(explode(',', $plist), $uids);
        $sql = 'SELECT '.$fields.' FROM `'.$table_name.'` WHERE uid IN('.$plist.')';
        $stmt = mysql::I($connection)->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        unset($app, $fields, $field_value, $table_name, $connection, $sql, $params, $plist, $stmt);
        return $res;
    }

    /**
     * @name 给用户新增一个登录身份
     * @desc
     * @param array $data 必须包含uid、type、identity，可包含access_token、expires_at、refresh_token
     * @return boolean
     */
    function insert_identity($data)
    {
        if (empty($data['type']) || empty($data['identity']) || empty($data['uid'])){
            return false;
        }
        empty($data['ctime']) && $data['ctime']=time();

        $this->insert_table($data);
        //把登录身份存入缓存
        $this->cache_user_identity($data['type'], $data['identity'], $data['uid']);
        return true;
    }

    /**
     * @name 删除用户的一个登录身份
     * @desc
     * @param string $type
     * @param string $identity
     * @param integer $uid
     * @return boolean
     */
    function delete_identity($type, $identity, $uid)
    {
        if (empty($type) || empty($identity) || empty($uid)){
            return false;
        }

        $this->delete(
            [
                'uid'=>$uid,
                'type'=>$type,
                'identity'=>$identity,
            ],
            ['uid'=>$uid]
        );
        //把登录身份存入缓存
        $this->delete_user_identity_cache($type, $identity, $uid);
        return true;
    }
}
