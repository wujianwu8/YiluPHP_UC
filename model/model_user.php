<?php
/*
 * 用户模型类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 20:16
 */

class model_user extends model
{
    protected $_table = 'user';
	//拆分表的方式，null表示不拆分，last_two_digits表示根据（如ID）末尾2位数拆分成100个表
	protected $_split_method = 'last_two_digits';
	//用于分表的字段名
	protected $_split_by_field = 'uid';

	/**
	 * @name 把一个手机号码转成昵称
	 * @desc 将中间数字转成字母
	 * @param string $mobile 手机号
	 * @return string
	 */
	public function mobile_to_nickname($mobile){
		$len = strlen($mobile);
		$per = floor($len/3);
		$str_len = floor(($len-$per)/2);
		$str = '';
		for ($i = 1; $i <= $str_len; $i++) {
			$str .= chr(rand(97, 122));
		}
		$nickname = substr($mobile,0,$per).$str.substr($mobile,$str_len+$per);
		unset($mobile, $len, $str_len, $per, $str, $i);
		return $nickname;
	}

	/**
	 * @name 检查一个昵称是否可用,如果已经被占用,则后面加一个数字,直到可用为止
	 * @desc 将中间数字转成字母
	 * @param string $mobile 手机号
	 * @return string
	 */
	public function get_an_available_nickname($nickname=''){
		if(!$nickname){
			$nickname = ten_to_54(str_replace('.','',microtime(true)).mt_rand(1,9999));
		}
		$tmp = $nickname;
		$isok = true;
		$time = 1;
		while($time<100 && redis_y::I()->hexists(REDIS_KEY_ALL_NICKNAME, md5($tmp))){
			$tmp = $nickname.'_'.mt_rand(1, 999999);
			$time++;
			$isok =false;
		}
		if(!$isok){
			$tmp = $nickname.'_'.ten_to_54(str_replace('.','',microtime(true)).mt_rand(1,9999));
		}
		//存入缓存中,后期使用一个定时任务检查昵称是否已经被使用,如果没使用则还原
		redis_y::I()->hset(REDIS_KEY_ALL_NICKNAME, md5($tmp), 1);
		redis_y::I()->hset(REDIS_KEY_NEW_NICKNAME, md5($tmp), $tmp);
		unset($nickname, $time, $isok);
		return $tmp;

	}

	/**
	 * @name 搜索用户
	 * @desc 分页读取
	 * @param array $where 查询条件，多个条件之间是并且的关系
	 * @param integer $page 页码
	 * @param integer $page_size 每页读取条数
     * @param string $field_value 用于分表的字段的值
     * @param string $fields 需要返回的字段
	 * @return array 数据列表
	 */
	function paging_select_search_user(array $where, int $page=1, int $page_size=10, $field_value=null, $fields=null)
	{
        $table_name = $this->sub_table($field_value);
        $connection = $this->sub_connection($field_value);
        if (isset($where['identity'])){
            if ($fields===null){
                $fields = ' u.*, i.`type`, i.`identity` ';
            }
            $sql = 'SELECT '.$fields.' FROM `'.$table_name.'` AS u ';
        }
	    else{
            if ($fields===null){
                $fields = ' u.* ';
            }
            $sql = 'SELECT '.$fields.' FROM `'.$table_name.'` AS u ';
        }
        if (isset($where['identity'])){
            $table_name = model_user_identity::I()->sub_table($field_value);
            $sql .= ', `'.$table_name.'` AS i ';
        }
        $where_array = [];
        $bind_value = [];
        if (isset($where['gender'])){
            $where_array[] = ' u.`gender`=:gender ';
            $bind_value[':gender'] = $where['gender'];
        }
        if (isset($where['nickname'])){
            $where_array[] = ' u.`nickname` LIKE :nickname ';
            $bind_value[':nickname'] = '%'.$where['nickname'].'%';
        }
        if (isset($where['position'])){
            $where_array[] = ' (u.`country` LIKE :country OR u.`province` LIKE :province OR u.`city` LIKE :city) ';
            $bind_value[':country'] = '%'.$where['position'].'%';
            $bind_value[':province'] = '%'.$where['position'].'%';
            $bind_value[':city'] = '%'.$where['position'].'%';
        }
        if (isset($where['uid'])){
            $where_array[] = ' u.`uid`=:uid ';
            $bind_value[':uid'] = $where['uid'];
        }
        if (isset($where['birthday_1'])){
            $where_array[] = ' u.`birthday`>=:birthday_1 ';
            $bind_value[':birthday_1'] = $where['birthday_1'];
        }
        if (isset($where['birthday_2'])){
            $where_array[] = ' u.`birthday`<=:birthday_2 ';
            $bind_value[':birthday_2'] = $where['birthday_2'];
        }
        if (isset($where['reg_time_1'])){
            $where_array[] = ' u.`ctime`>=:reg_time_1 ';
            $bind_value[':reg_time_1'] = $where['reg_time_1'];
        }
        if (isset($where['reg_time_2'])){
            $where_array[] = ' u.`ctime`<=:reg_time_2 ';
            $bind_value[':reg_time_2'] = $where['reg_time_2'];
        }
        if (isset($where['last_active_1'])){
            $where_array[] = ' u.`last_active`>=:last_active_1 ';
            $bind_value[':last_active_1'] = $where['last_active_1'];
        }
        if (isset($where['last_active_2'])){
            $where_array[] = ' u.`last_active`<=:last_active_2 ';
            $bind_value[':last_active_2'] = $where['last_active_2'];
        }
        if (isset($where['status'])){
            $where_array[] = ' u.`status`=:status ';
            $bind_value[':status'] = $where['status'];
        }
        if (isset($where['identity'])){
            $where_array[] = ' u.`uid`=i.uid AND i.identity LIKE :identity ';
            $bind_value[':identity'] = '%'.$where['identity'].'%';
        }
        if ($where_array){
            $sql .= ' WHERE '.implode(' AND ', $where_array);
        }
        $sql .= ' ORDER BY u.ctime DESC LIMIT :start, :page_size ';
        $start = ($page-1)*$page_size;
        $start<0 && $start = 0;
        $bind_value[':start'] = $start;
        $bind_value[':page_size'] = $page_size;
        $stmt = mysql::I($connection)->prepare($sql);
        foreach ($bind_value as $key => $value){
            //第三个参数data_type，使用 PDO::PARAM_* 常量明确地指定参数的类型，如：
            //PDO::PARAM_INT、PDO::PARAM_STR、PDO::PARAM_BOOL、PDO::PARAM_NULL
            $stmt->bindValue($key, $value, is_numeric($value) ? PDO::PARAM_INT : (is_string($value) ? PDO::PARAM_STR :
                (is_bool($value) ? PDO::PARAM_BOOL : (is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR))));
        }
        $stmt->execute();
//		PDO::FETCH_ASSOC          从结果集中获取以列名为索引的关联数组。
//  	PDO::FETCH_NUM             从结果集中获取一个以列在行中的数值偏移量为索引的值数组。
//  	PDO::FETCH_BOTH            这是默认值，包含上面两种数组。
//  	PDO::FETCH_OBJ               从结果集当前行的记录中获取其属性对应各个列名的一个对象。
//  	PDO::FETCH_BOUND        使用fetch()返回TRUE，并将获取的列值赋给在bindParm()方法中指 定的相应变量。
//  	PDO::FETCH_LAZY            创建关联数组和索引数组，以及包含列属性的一个对象，从而可以在这三种接口中任选一种。
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        unset($where, $page, $page_size, $where_array, $bind_value, $field_value, $table_name, $connection, $sql, $start, $stmt);
        return $res;
	}

    /**
     * @name 根据多个用户ID查询用户的信息
     * @desc 分页读取
     * @param array $uids 多个用户ID的数据
     * @param string $fields 需要返回的字段
     * @param string $field_value 用于分表的字段的值
     * @return array 数据列表
     */
    function select_user_info_by_multi_uids(array $uids, string $fields='*', $field_value=null)
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
     * @name 根据昵称或用户ID搜索投诉人用户
     * @desc 分页读取
     * @param string $keyword 多个用户ID的数据
     * @param string $fields 需要返回的字段
     * @param string $limit 最多返回的结果数
     * @return array 数据列表
     */
    function select_user_by_uid_or_nickname(string $keyword, string $fields='*', $limit=500){
        if (empty($GLOBALS['config']['split_table']) || empty($this->_split_method)){
            $table_name = $this->sub_table();
            $connection = $this->sub_connection();
            $sql = 'SELECT '.$fields.' FROM '.$table_name.' WHERE uid=:uid OR nickname LIKE :nickname LIMIT '.intval($limit);
            $params = [
                ':uid' => $keyword,
                ':nickname' => '%'.$keyword.'%',
            ];
            $stmt = mysql::I($connection)->prepare($sql);
            $stmt->execute($params);
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }
        else{
            $params = [
                ':uid' => $keyword,
                ':nickname' => '%'.$keyword.'%',
            ];
            $res = [];
            for ($i=0; $i<100; $i++){
                $table_name = $this->sub_table($i);
                $connection = $this->sub_connection($i);
                $left_limit = $limit-count($res);
                $sql = 'SELECT '.$fields.' FROM '.$table_name.' WHERE uid=:uid OR nickname LIKE :nickname LIMIT '.$left_limit;
                $stmt = mysql::I($connection)->prepare($sql);
                $stmt->execute($params);
                if($tmp = $stmt->fetchAll(PDO::FETCH_ASSOC)){
                    $res = array_merge($res, $tmp);
                }
                if (count($res)>=$limit){
                    break;
                }
            }
            return $res;
        }
    }

}
