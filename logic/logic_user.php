<?php
/*
 * 用户类
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/17
 * Time: 21:19
 */

class logic_user
{
	public function __construct()
	{
	}

	public function __destruct()
	{
	}

    /**
     * @name 创建一个新用户
     * @desc
     * @param string $data 用户信息
     * @return int 成功返回用户ID,失败抛出异常
	 * @throws
     */
    public function create_user(&$data)
    {
		if (!empty($GLOBALS['config']['split_table']) && !isset($data[$GLOBALS['app']->model_user->get_split_by_field()])){
			throw new Exception('用户的MODEL中缺少分表用的字段值:'.$GLOBALS['app']->model_user->get_split_by_field(), CODE_ERROR_IN_MODEL);
		}
		if (!empty($GLOBALS['config']['split_table']) && !isset($data[$GLOBALS['app']->model_user_identity->get_split_by_field()])){
			throw new Exception('用户身份的MODEL中缺少分表用的字段值:'.$GLOBALS['app']->model_user_identity->get_split_by_field(), CODE_ERROR_IN_MODEL);
		}

		$connections = [ $GLOBALS['app']->model_user->get_connection() ];
		if(false === array_search($GLOBALS['app']->model_user_identity->get_connection(), $connections)){
			//把用户身份主表的数据库连接名存入数组
			$connections[] = $GLOBALS['app']->model_user_identity->get_connection();
		}

		$field_value = null;
		if ( !empty($GLOBALS['config']['split_table']) ){
			$field_value = $data[$GLOBALS['app']->model_user->get_split_by_field()];
		}
		$connection = $GLOBALS['app']->model_user->sub_connection($field_value);
		if(false === array_search($connection, $connections)){
			//把用户分表的数据库连接名存入数组
			$connections[] = $connection;
		}

		$field_value = null;
		if ( !empty($GLOBALS['config']['split_table']) ){
			$field_value = $data[$GLOBALS['app']->model_user_identity->get_split_by_field()];
		}
		$connection = $GLOBALS['app']->model_user_identity->sub_connection($field_value);
		if(false === array_search($connection, $connections)){
			//把用户身份分表的数据库连接名存入数组
			$connections[] = $connection;
		}

		if(empty($data['uid'])) {
			if (!$data['uid'] = $GLOBALS['app']->uuid->newUserId()) {
				throw new Exception('生成用户ID失败', CODE_FAIL_TO_GENERATE_UID);
			}
		}

		try {
			$time = time();
			foreach($connections as $connection) {
				//开始事务
				$GLOBALS['app']->mysql($connection)->beginTransaction();
				unset($connection);
			}
			$info = [
					'uid' => $data['uid'],
					'last_active' => $time,
					'ctime' => $time,
			];
			$fields = ['nickname', 'gender', 'password', 'birthday', 'status', 'avatar', 'country', 'province', 'city'];
			foreach ($fields as $item) {
				if (isset($data[$item])) {
					if ($item == 'password') {
						$info['salt'] = uniqid();
						$info['password'] = md5($data[$item] . $info['salt']);
					} else {
						$info[$item] = $data[$item];
					}
				}
				unset($item);
			}
			$GLOBALS['app']->model_user->insert_table($info);

			$info = [
					'uid' => $data['uid'],
					'type' => $data['type'],
					'identity' => $data['identity'],
					'ctime' => $time,
			];
			$fields = ['access_token', 'expires_at', 'refresh_token'];
			foreach ($fields as $item) {
				if (isset($data[$item])) {
					$info[$item] = $data[$item];
				}
				unset($item);
			}

            $GLOBALS['app']->model_user_identity->insert_identity($info);

			foreach($connections as $connection) {
				//开始事务
				$GLOBALS['app']->mysql($connection)->commit();
				unset($connection);
			}
			unset($time, $fields, $info, $connections, $item);
			return $data['uid'];
		}
		catch(Exception $e){
			foreach($connections as $connection) {
				//开始事务
				$GLOBALS['app']->mysql($connection)->rollBack();
			}
			unset($time, $fields, $info, $connections, $item);
			throw new Exception('创建账户失败:'.$e->getMessage(), $e->getCode());
		}
    }

	/**
	 * @name 根据uid登录用户
	 * @desc
	 * @param integer $uid 用户ID
	 * @return array 成功返回用户信息,失败则抛出异常
	 * @throws
	 */
	public function login_by_uid(&$uid){
		if(!$uid){
			throw new Exception('参数UID错误:'.$uid, CODE_ERROR_IN_SERVICE);
		}
		if(!$user_info = $GLOBALS['app']->model_user->find_table(['uid'=>$uid], '*', $uid)){
			throw new Exception('用户信息不存在:'.$uid, CODE_ERROR_IN_SERVICE);
		}

		$this->create_login_session($user_info);
		return $user_info;
	}

    /**
     * @name 根据uid获取用户的安全信息
     * @desc 安全信息指可以对外公开的信息
     * @param integer $uid 用户ID
     * @return array 成功返回用户信息
     * @throws
     */
    public function find_user_safe_info($uid){
        return $GLOBALS['app']->model_user->find_table(['uid'=>$uid],
            'uid,nickname,gender,birthday,status,avatar,country,province,city,last_active,ctime', $uid);
    }

    /**
     * @name 退出登录
     * @desc
     * @return boolean
     * @throws
     */
    public function destroy_login_session($vk=''){
        if(empty($vk) && !empty($_COOKIE['vk'])) {
            $vk = $_COOKIE['vk'];
        }
        if(!empty($vk)) {
            $cache_key_vk = REDIS_KEY_LOGIN_USER_INFO_BY_VK.$vk;
            if($info = $GLOBALS['app']->redis()->hgetall($cache_key_vk)){
                $cache_key_uid = REDIS_KEY_LOGIN_USER_INFO_BY_UID.$info['uid'];
                $GLOBALS['app']->redis()->del($cache_key_vk);
                $GLOBALS['app']->redis()->del($cache_key_uid);
            }
        }
        session_destroy();
        return true;
    }

	/**
	 * @name 创建用户的登录状态
	 * @desc
	 * @param array $user_info 用户信息
	 * @param boolean $remember_me 是否长期保持登录状态
	 * @return boolean 失败则抛出异常
	 * @throws
	 */
	public function create_login_session(&$user_info, $remember_me=false){
		$arr = [
			'uid' => $user_info['uid'],
			'nickname' => $user_info['nickname'],
			'avatar' => isset($user_info['avatar']) ? $user_info['avatar'] : '',
			'gender' => isset($user_info['gender']) ? $user_info['gender'] : 'male',
            'last_active' => time(),
            'remember' => $remember_me?1:0,
		];
        if(empty($_COOKIE['vk'])){
            write_applog('ERROR', '缺少标识访问用户的cookie: vk, $user_info='.json_encode($user_info));
            throw new Exception('登录失败', CODE_ERROR_IN_SERVICE);
        }
        $vk = $_COOKIE['vk'];
        $cache_key_vk = REDIS_KEY_LOGIN_USER_INFO_BY_VK.$vk;
        $cache_key_uid = REDIS_KEY_LOGIN_USER_INFO_BY_UID.$user_info['uid'];
        $GLOBALS['app']->redis()->hmset($cache_key_vk, $arr);
        $arr['vk'] = $vk;
        $GLOBALS['app']->redis()->hmset($cache_key_uid, $arr);
		if($remember_me){
            $GLOBALS['app']->redis()->expire($cache_key_vk, TIME_60_DAY);
            $GLOBALS['app']->redis()->expire($cache_key_uid, TIME_60_DAY);
		}
		//登录时长跟随浏览器状态和session的时长
		else {
            $GLOBALS['app']->redis()->expire($cache_key_vk, TIME_30_MIN);
            $GLOBALS['app']->redis()->expire($cache_key_uid, TIME_30_MIN);
		}
        $user_info['tlt'] = $this->create_login_tlt($user_info['uid'], client_ip());
		unset($arr);
		return true;
	}

    /**
     * @name 创建用户的临时登录令牌tlt
     * @desc 安全信息指可以对外公开的信息
     * @param integer $uid 用户ID
     * @param string $client_ip 用户客户端IP
     * @return string 成功返回tlt
     * @throws
     */
    public function create_login_tlt($uid, $client_ip){
        $tlt = md5(microtime() . $uid . $client_ip . uniqid());
        $cache_key = REDIS_KEY_USER_LOGIN_TLT.$tlt;
        $arr = [
            'uid' => $uid,
            'client_ip' => $client_ip
        ];
        $GLOBALS['app']->redis()->set($cache_key, json_encode($arr));
        $GLOBALS['app']->redis()->expire($cache_key, TIME_30_SEC);
        unset($cache_key, $arr);
        return $tlt;
    }

    /**
     * @name 获取当前用户的信息，可用于判断当前用户是否登录
     * @desc 不读数据库，只读存在缓存中的基本信息
     * @return array/null
     * @throws
     */
    public function get_current_user_info()
    {
        if(empty($_COOKIE['vk'])){
            return null;
        }
        $vk = $_COOKIE['vk'];
        return $GLOBALS['app']->redis()->hgetall(REDIS_KEY_LOGIN_USER_INFO_BY_VK.$vk);
    }

    /**
     * @name 通过vk获取登录用户的信息，可用于判断用户是否登录
     * @desc 不读数据库，只读存在缓存中的基本信息
     * @param string $vk cookie vk的值
     * @return array/null
     * @throws
     */
    public function get_login_user_info_by_vk($vk)
    {
        return $GLOBALS['app']->redis()->hgetall(REDIS_KEY_LOGIN_USER_INFO_BY_VK.$vk);
    }

    /**
     * @name 通过uid获取登录用户的信息，可用于判断用户是否登录
     * @desc 不读数据库，只读存在缓存中的基本信息
     * @param integer $uid 用户id
     * @return array/null
     * @throws
     */
    public function get_login_user_info_by_uid($uid)
    {
        return $GLOBALS['app']->redis()->hgetall(REDIS_KEY_LOGIN_USER_INFO_BY_UID.$uid);
    }

    /**
     * @name 延长登录用户在缓存中的信息的有效期
     * @desc
     * @param integer $uid 用户id
     * @param string $vk cookie vk的值
     * @param integer $expire 剩余有效时间，秒
     * @return boolean true
     * @throws
     */
    public function keep_login_user_alive($uid, $vk, $expire)
    {
        $time = time();
        if ($uid){
            $GLOBALS['app']->redis()->hset(REDIS_KEY_LOGIN_USER_INFO_BY_UID.$uid, 'last_active', $time);
            $GLOBALS['app']->redis()->expire(REDIS_KEY_LOGIN_USER_INFO_BY_UID.$uid, $expire);
            $where = ['uid'=>$uid];
            $data = ['last_active'=>$time];
            $GLOBALS['app']->model_user->update_table($where, $data);
            unset($where, $data);
        }
        if ($vk){
            $GLOBALS['app']->redis()->hset(REDIS_KEY_LOGIN_USER_INFO_BY_VK.$vk, 'last_active', $time);
            $GLOBALS['app']->redis()->expire(REDIS_KEY_LOGIN_USER_INFO_BY_VK.$vk, $expire);
        }
        unset($uid, $vk, $expire, $time);
        return true;
    }

    /**
     * @name 更新当前登录用户的信息
     * @desc 传啥字段就更新啥字段
     * @param array $data 需要更新的信息
     * @return array/null
     * @throws
     */
    public function update_current_user_info($data)
    {
        if(empty($_COOKIE['vk'])){
            unset($data);
            return null;
        }
        $vk = $_COOKIE['vk'];
        $cache_key_vk = REDIS_KEY_LOGIN_USER_INFO_BY_VK.$vk;
        if($current_infor = $GLOBALS['app']->redis()->hgetall($cache_key_vk)){
            isset($data['nickname']) && $current_infor['nickname'] = $data['nickname'];
            isset($data['avatar']) && $current_infor['avatar'] = $data['avatar'];
            isset($data['gender']) && $current_infor['gender'] = $data['gender'];
            $GLOBALS['app']->redis()->hmset($cache_key_vk, $current_infor);
            $cache_key_uid = REDIS_KEY_LOGIN_USER_INFO_BY_UID.$current_infor['uid'];
            if($infor = $GLOBALS['app']->redis()->hgetall($cache_key_uid)){
                isset($data['nickname']) && $infor['nickname'] = $data['nickname'];
                isset($data['avatar']) && $infor['avatar'] = $data['avatar'];
                isset($data['gender']) && $infor['gender'] = $data['gender'];
                $GLOBALS['app']->redis()->hmset($cache_key_uid, $infor);
            }
            unset($cache_key_uid, $infor);
        }
        unset($data, $cache_key_vk);
        return $current_infor;
    }

	/**
	 * @name 检测用户身份(登录账户)的类型
	 * @desc
	 * @param string $identity 用户身份(登录账户)
	 * @return string username为用户名,mobile为手机号,email为电子邮箱
	 */
	public function get_identity_type(&$identity){
		if(empty($identity)){
			throw new Exception('$identity参数为空错误', CODE_ERROR_IN_SERVICE);
		}
		if(preg_match('/^\d*\-*\d+$/', $identity)){
			return 'mobile';
		}
		if( strpos($identity, '@')>0 ){
			return 'email';
		}
		return 'username';
	}

	/**
	 * @name 修改用户信息
	 * @desc
	 * @param array $where 更新条件,如果有做分表,$where中必须包含用来分表的字段
	 * @param array $data 需要修改的数据
     * @return boolean
	 * @throws
	 */
	public function update_user_info(&$where, $data)
	{
		if (!empty($GLOBALS['config']['split_table']) && !isset($where[$GLOBALS['app']->model_user->get_split_by_field()])){
			throw new Exception('用户的MODEL中缺少分表用的字段值:'.$GLOBALS['app']->model_user->get_split_by_field(), CODE_ERROR_IN_SERVICE);
		}
		if(isset($data['password'])){
			if(!isset($data['salt'])){
				$data['salt'] = uniqid();
			}
			$data['password'] = md5($data['password'].$data['salt']);
		}

		global $app;
		return $app->model_user->update_table($where, $data);
	}

    /**
     * @name 已登录用户自动跳转
     * @desc
     * @param boolean $return 是否不立即跳转而返回跳转地址
     * @param string $tlt 临时登录令牌
     * @throws
     */
	public function auto_jump($return=false, $tlt=''){
	    $url_add_params = function ($url, $params){
            $temp = parse_url($url);
            if (!empty($temp['host']) && $temp['host']!=$_SERVER['HTTP_HOST']){
                $url_well = explode('#', $url);
                $url = explode('?', $url_well[0]);
                $url_params = [];
                if (count($url)>1){
                    $temp = explode('&', $url[1]);
                    foreach ($temp as $value){
                        $value = explode('=', $value);
                        $url_params[$value[0]] = isset($value[1])?$value[1]:'';
                    }
                }
                foreach ($params as $key=>$value){
                    $url_params[$key] = $value;
                }
                $temp = [];
                foreach ($url_params as $key=>$value){
                    $temp[] = $key.'='.$value;
                }
                $url = $url[0].'?'.implode('&', $temp);
                if (count($url_well)>1){
                    $url .= '#'.$url_well[1];
                }
                unset($temp, $url_params, $key, $value, $url_well);
            }
            return $url;
        };

	    //检查URL中的redirect_uri参数
        if (!empty($_REQUEST['redirect_uri'])){
            $redirect_uri = trim($_REQUEST['redirect_uri']);
            if ($redirect_uri!=''){
                $redirect_uri = $tlt ? $url_add_params($redirect_uri, ['tlt'=>$tlt]) : $redirect_uri;
                if ($return){
                    return $redirect_uri;
                }
                header('Location: '.$redirect_uri);
                exit;
            }
        }
        //检查cookie中的redirect_uri参数
        if (!empty($_COOKIE['redirect_uri'])){
            $redirect_uri = trim($_COOKIE['redirect_uri']);
            if ($redirect_uri!=''){
                $redirect_uri = $tlt ? $url_add_params($redirect_uri, ['tlt'=>$tlt]) : $redirect_uri;
                if ($return){
                    return $redirect_uri;
                }
                header('Location: '.$redirect_uri);
                exit;
            }
        }

        //检查referer值
        $referer = !empty($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER'] : null;
        if ($referer){
            $urlInfo = parse_url($referer);
            if (!empty($urlInfo['host']) && strtolower($urlInfo['host']) === strtolower($_SERVER['HTTP_HOST']) ) {
                //设置不回跳的URI
                $except = [
                    '/find_password',
                    '/sign/up',
                    '/sign/bind_account',
                ];
                if (!in_array($urlInfo['path'], $except)){
                    $referer = $tlt ? $url_add_params($referer, ['tlt'=>$tlt]) : $referer;
                    if ($return){
                        return $referer;
                    }
                    header('Location: '.$referer);
                    exit;
                }
            }
        }
        //默认跳转到dashboard
        if ($return){
            return '/dashboard';
        }
        header('Location: /dashboard');
        exit;
    }

	/**
	 * @name 搜索用户
	 * @desc 分页读取
	 * @param array $where 查询条件，多个条件之间是并且的关系
	 * @param integer $page 页码
	 * @param integer $page_size 每页读取条数
	 * @return array 数据列表
	 */
	function paging_select_search_user(array $where, int $page=1, int $page_size=10)
	{
		global $app;
		$total_user_count = $app->model_user_identity->get_user_count();
        $user_list = [];
        if (empty($GLOBALS['config']['split_table']) || (!empty($GLOBALS['config']['split_table']) && count($where)==0 && $page*$page_size<=$total_user_count) ) {
            $user_list = $app->model_user->paging_select_search_user($where, $page, $page_size);
        }
        else if (!empty($GLOBALS['config']['split_table'])) {
            $start = ($page-1)*$page_size;
            $start<0 && $start=0;
            $end = $start+$page_size;
            //如果有分表，则分表中搜索
            $cache_key = REDIS_KEY_SEARCH_USER_RESULT.md5(json_encode($where));
            if (!$app->redis()->exists($cache_key)){
                $app->redis()->del($cache_key);
                $step = 1000000;
                $have_data = false;
                for ($i=0; $i<100; $i++){
                    do{
                        $user_list = $app->model_user->paging_select_search_user($where, $page, $step, $i, ' u.uid, u.ctime ');
                        if ($have_data === false && $user_list){
                            $have_data = true;
                        }
                        foreach ($user_list as $item){
                            $app->redis()->zadd($cache_key, $item['ctime'].mt_rand(10000,99999), $item['uid']);
                        }
                    }
                    while(count($user_list)>=$step);
                }
                if ($have_data){
                    $app->redis()->EXPIRE($cache_key, TIME_10_MIN);
                }
                else{
                    return [];
                }
            }
            if(!$user_ids = $app->redis()->zrange($cache_key, $start, $end)){
                return [];
            }

            $user_list = [];
            //读取用户信息
            $users = $this->select_user_info_by_multi_uids($user_ids);
            if (isset($where['identity'])) {
                $user_identity = $this->select_user_identity_by_multi_uids($user_ids, 'uid,`type`,`identity`');
            }
            foreach ($user_ids as $user_id){
                if (isset($where['identity'])){
                    $users[$user_id]['type'] = $users[$user_id]['identity'] = '';
                    foreach ($user_identity as $identity){
                        if ($identity['uid']==$user_id && strpos($identity['identity'], $where['identity'])!==false){
                            $users[$user_id]['type'] = $identity['type'];
                            $users[$user_id]['identity'] = $identity['identity'];
                        }
                    }
                }
                $user_list[] = $users[$user_id];
            }
        }
		return $user_list;
	}

    /**
     * @name 根据多个用户ID查询用户的信息
     * @desc
     * @param array $uids 多个用户ID的数组
     * @param string $fields 需要返回的字段
     * @return array 数据列表
     */
    function select_user_info_by_multi_uids(array $uids, string $fields='*')
    {
        global $app;
        if (empty($GLOBALS['config']['split_table'])) {
            $users = $app->model_user->select_user_info_by_multi_uids($uids, $fields);
        }
        else{
            //分类用户ID
            $classify_uids = [];
            foreach ($uids as $uid){
                $tmp = intval(substr($uid, -2));
                if (isset($classify_uids[$tmp])){
                    $classify_uids[$tmp][] = $uid;
                }
                else{
                    $classify_uids[$tmp] = [$uid];
                }
            }
            $users = [];
            foreach ($classify_uids as $suffix => $uid_arr) {
                $tmp = $app->model_user->select_user_info_by_multi_uids($uid_arr, $fields, $suffix);
                foreach ($tmp as $user){
                    $users[$user['uid']] = $user;
                }
            }
        }
        return $users;
    }

    /**
     * @name 根据多个用户ID查询用户的身份信息
     * @desc
     * @param array $uids 多个用户ID的数组
     * @param string $fields 需要返回的字段
     * @return array 数据列表
     */
    function select_user_identity_by_multi_uids(array $uids, string $fields='*')
    {
        global $app;
        if (empty($GLOBALS['config']['split_table'])) {
            $users = $app->model_user->select_user_info_by_multi_uids($uids, $fields);
        }
        else{
            //分类用户ID
            $classify_uids = [];
            foreach ($uids as $uid){
                $tmp = intval(substr($uid, -2));
                if (isset($classify_uids[$tmp])){
                    $classify_uids[$tmp][] = $uid;
                }
                else{
                    $classify_uids[$tmp] = [$uid];
                }
            }
            $users = [];
            foreach ($classify_uids as $suffix => $uid_arr) {
                if($tmp = $app->model_user_identity->select_user_identity_by_multi_uids($uid_arr, $fields, $suffix)){
                    $users = array_merge($users, $tmp);
                }
            }
        }
        return $users;
    }

    /**
     * @name 给当前登录用户绑定第三方账号
     * @desc
     * @param string $identity_type
     * @param string $identity
     * @param boolean $return_json 是否返回JSON
     * @return array 数据列表
     */
    function bind_outer_account($identity_type, $identity, $return_json=false){
        global $app;

        if (empty($GLOBALS['self_info']['uid'])) {
            $msg = '您未登录或登录已超时，请重新登录';
            if ($return_json){
                return_json(31, $msg);
            }
            return_code(CODE_UNDEFINED_ERROR_TYPE,$msg);
        }

        //如果该第三方用户已经绑定其它账号
        if($uid = $app->model_user_identity->find_uid_by_identity($identity_type, $identity)) {
            if ($uid == $GLOBALS['self_info']['uid']){
                if ($return_json){
                    return_json(34, '绑定成功');
                }
                header('Location: /setting/user_info');
                unset($uid, $identity_data, $identity_type, $identity, $self_uid);
                exit;
            }
            $user_info = $app->model_user->find_table(['uid' => $uid], 'nickname',$uid);
            $msg = '该'.$app->lang('identity_type_user_'.$identity_type).'已经绑定到用户：'
                .$user_info['nickname'].'，不可以再绑定到其他用户';
            if ($return_json){
                return_json(30, $msg);
            }
            return_code(CODE_UNDEFINED_ERROR_TYPE,$msg);
        }
        $self_uid = $GLOBALS['self_info']['uid'];
        //检查当时登录用户是否已经绑定第三方账号
        //检查当前内部用户是否已经绑定过相同类型的外部账户
        if($app->model_user_identity->find_table(
            [
                'uid' => $self_uid,
                'type' => $identity_type
            ],
            'uid', $self_uid)){
            unset($uid, $self_uid);
            $msg = '您已经绑定其它'.$app->lang('identity_type_user_'.$identity_type).',不能再绑定';
            if ($return_json){
                return_json(32, $msg);
            }
            return_code(CODE_UNDEFINED_ERROR_TYPE,$msg);
        }
        //绑定到当前登录用户
        $identity_data = [
            'uid' => $self_uid,
            'type' => $identity_type,
            'identity' => $identity,
            'ctime' => time(),
        ];
        $app->model_user_identity->insert_identity($identity_data);

        if ($return_json){
            return_json(33, '绑定成功');
        }
        header('Location: /setting/user_info');
        unset($uid, $identity_data, $identity_type, $identity, $self_uid);
        exit;
    }
}
