<?php
/*
 * 与用户中心系统对接的模型类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * Date: 2021/01/21
 * Time: 22:33
 */

class model_user_center
{
    //存储单例
    private static $_instance = null;

    /**
     * 获取单例
     * @return model_user_center
     */
    public static function I(){
        if (!static::$_instance){
            return static::$_instance = new self();
        }
        return static::$_instance;
    }

    /**
     * @name 根据vk检查登录状态
     * @desc 用于根据用户cookie中的vk值判断是否已登录
     * @param string $vk 必选 用户cookie中的vk值
     * @param integer $keep_alive 可选 0不延长登录时效，1延长登录时效，默认为1
     * @return array|null 返回用户中心接口返回的完整结果，失败或网络异常返回null
     * 成功返回示例：
     * {
     *   "code": 0,          // 0已登录，-1未登录
     *   "msg": "已经登录",
     *   "data": {
     *     "user_info": {
     *       "uid": 123,              // int 用户ID
     *       "nickname": "Jim",       // string 昵称
     *       "avatar": "https://...", // string 头像URL
     *       "gender": "male",        // string 性别 male/female
     *       "last_active": 1514567890, // int 最后活跃时间戳
     *       "remember": 0,           // int 是否记住登录 0/1
     *       "keep_alive": 1514567890  // int 保活时间戳（仅keep_alive=1时返回）
     *     }
     *   }
     * }
     * @throws
     */
    public function check_login_by_vk($vk, $keep_alive=1){
        return $this->_curl_post('/internal/check_login_by_vk', ['vk'=>$vk,'keep_alive'=>$keep_alive]);
    }

    /**
     * @name 根据uid检查登录状态
     * @desc 用于根据用户ID判断是否已登录
     * @param integer $uid 必选 用户ID
     * @param integer $keep_alive 可选 0不延长登录时效，1延长登录时效，默认为1
     * @return array|null 返回用户中心接口返回的完整结果，失败或网络异常返回null
     * 成功返回示例：
     * {
     *   "code": 0,          // 0已登录，-1未登录
     *   "msg": "已经登录",
     *   "data": {
     *     "user_info": {
     *       "uid": 123,              // int 用户ID
     *       "nickname": "Jim",       // string 昵称
     *       "avatar": "https://...", // string 头像URL
     *       "gender": "male",        // string 性别 male/female
     *       "last_active": 1514567890, // int 最后活跃时间戳
     *       "remember": 0,           // int 是否记住登录 0/1
     *       "vk": "abc123",          // string 用户的vk值
     *       "keep_alive": 1514567890  // int 保活时间戳（仅keep_alive=1时返回）
     *     }
     *   }
     * }
     * @throws
     */
    public function check_login_by_uid($uid, $keep_alive=1){
        return $this->_curl_post('/internal/check_login_by_uid', ['uid'=>$uid,'keep_alive'=>$keep_alive]);
    }

    /**
     * @name 根据临时登录令牌检查登录状态
     * @desc 用于根据tlt获取登录用户信息，TLT是临时登录令牌(Temporary Login Token)，30秒内有效
     * @param string $tlt 必选 临时登录令牌，32位字符串
     * @return array|null 返回用户中心接口返回的完整结果，失败或网络异常返回null
     * 成功返回示例：
     * {
     *   "code": 0,          // 0已登录，-1未登录
     *   "msg": "已经登录",
     *   "data": {
     *     "user_info": {
     *       "uid": 123,              // int 用户ID
     *       "nickname": "Jim",       // string 昵称
     *       "avatar": "https://...", // string 头像URL
     *       "gender": "female",      // string 性别 male/female
     *       "birthday": "2011-08-21",// string 生日 Y-m-d格式
     *       "status": 0,             // int 用户状态
     *       "country": "中国",       // string 国家
     *       "province": "江西省",    // string 省份
     *       "city": "赣州市",        // string 城市
     *       "last_active": 1514567890, // int 最后活跃时间戳
     *       "ctime": 1494567890,     // int 注册时间戳
     *       "register_invitation_link_id": 0, // int 注册时的邀请链接ID
     *       "register_inviter_uid": 0,        // int 注册时的邀请人UID
     *       "client_ip": "127.0.0.1" // string 登录时的客户端IP
     *     }
     *   }
     * }
     * @throws
     */
    public function check_login_by_tlt($tlt){
        return $this->_curl_post('/internal/check_login_by_tlt', ['tlt'=>$tlt]);
    }

    /**
     * @name 获取用户菜单列表
     * @desc 根据用户ID读取该用户可见菜单，已根据权限过滤，按层级排列
     * @param integer $uid 必选 用户ID
     * @return array 菜单列表，每个菜单项结构如下：
     * [
     *   {
     *     "id": 1,                // int 菜单ID
     *     "parent_menu": 0,       // int 父菜单ID，0为顶级菜单
     *     "lang_key": "菜单名称", // string 菜单名称（已翻译为当前语言）
     *     "url": "/user/list",    // string 菜单链接地址
     *     "permission": "app_id:permission_key", // string 访问所需权限，格式为app_id:permission_key
     *     "position": 0,          // int 菜单位置
     *     "weight": 1,            // int 排序权重，值越小越靠前
     *     "ctime": 1494567890,    // int 创建时间戳
     *     "children": [...]       // array 子菜单列表，结构与父菜单相同，可嵌套多层
     *   }
     * ]
     * @throws validate_exception 获取失败时抛出异常
     */
    public function select_menu_list($uid){
        $result = $this->_curl_post('/internal/select_menu_list', ['uid'=>$uid]);
        if ($result && $result['code']==0){
            return $result['data']['menu_list'];
        }
        else{
            write_applog('ERROR', '用uid从用户中心获取用户菜单失败，$uid=' . $uid
                .'，返回数据：'.json_encode($result, JSON_UNESCAPED_UNICODE));
            throw new validate_exception('获取用户菜单失败', CODE_NO_AUTHORIZED);
        }
    }

    /**
     * @name 根据用户名获取用户ID
     * @desc 根据可登录的用户名查找对应的uid
     * @param string $username 必选 用户名
     * @return array|null 返回用户中心接口返回的完整结果，失败或网络异常返回null
     * 成功返回示例：
     * {
     *   "code": 0,          // 0获取成功，1参数错误，2用户不存在
     *   "msg": "获取成功",
     *   "data": {
     *     "uid": 123         // int 用户ID
     *   }
     * }
     * @throws
     */
    public function find_uid_by_username($username){
        return $this->_curl_post('/internal/find_uid_by_username', ['username'=>$username]);
    }

    /**
     * @name 根据用户ID获取用户名
     * @desc 根据uid查找可登录的用户名（username类型的identity）
     * @param integer $uid 必选 用户ID
     * @return string|null 成功返回用户名字符串（如"jimwu"），用户不存在或失败返回null
     * @throws
     */
    public function find_username_by_uid($uid){
        $result = $this->_curl_post('/internal/find_username_by_uid', ['uid'=>$uid]);
        if ($result && $result['code']==0){
            return $result['data']['username'];
        }
        else{
            write_applog('ERROR', '用uid从用户中心获取用户名时失败，$uid=' . $uid
                .'，返回数据：'.json_encode($result, JSON_UNESCAPED_UNICODE));
            return null;
        }
    }

    /**
     * @name 根据用户ID获取用户资料
     * @desc 返回用户的安全信息（可对外公开的信息），不含密码等敏感字段
     * @param integer $uid 必选 用户ID
     * @return array|null 成功返回用户资料数组，用户不存在或失败返回null
     * 成功返回示例：
     * {
     *   "uid": 123,              // int 用户ID
     *   "nickname": "Jim",       // string 昵称
     *   "avatar": "https://...", // string 头像URL
     *   "gender": "female",      // string 性别 male/female
     *   "birthday": "2011-08-21",// string 生日 Y-m-d格式
     *   "status": 0,             // int 用户状态
     *   "country": "中国",       // string 国家
     *   "province": "江西省",    // string 省份
     *   "city": "赣州市",        // string 城市
     *   "last_active": 1514567890, // int 最后活跃时间戳
     *   "ctime": 1494567890,     // int 注册时间戳
     *   "register_invitation_link_id": 0, // int 注册时的邀请链接ID
     *   "register_inviter_uid": 0         // int 注册时的邀请人UID
     * }
     * @throws
     */
    public function find_user_info_by_uid($uid){
        $result = $this->_curl_post('/internal/find_user_info_by_uid', ['uid'=>$uid]);
        if ($result && $result['code']==0){
            return $result['data']['user_info'];
        }
        else{
            write_applog('ERROR', '从用户中心获取用户时失败，$uid=' . $uid
                .'，返回数据：'.json_encode($result, JSON_UNESCAPED_UNICODE));
            return null;
        }
    }

    /**
     * @name 根据邀请码查询邀请链接
     * @desc 返回用户中心内部接口中的invitation_link数据
     * @param string $invite_code 必选 邀请码
     * @return array|null 成功返回邀请链接数组，邀请码不存在或失败返回null
     * 成功返回示例：
     * {
     *   "id": 1,                  // int 邀请链接ID
     *   "uid": 123,               // int 邀请人用户ID
     *   "scene": "register",      // string 邀请场景
     *   "invite_code": "Ab3xYz",  // string 邀请码
     *   "cookie_ttl": 1296000,    // int cookie有效期，单位秒，默认15天(1296000)
     *   "remark": "推广活动",     // string 备注
     *   "ctime": 1494567890,      // int 创建时间戳
     *   "mtime": 1494567890       // int 最后修改时间戳
     * }
     * @throws
     */
    public function find_invitation_link_by_code($invite_code)
    {
        $result = $this->_curl_post('/internal/find_invitation_link_by_code', ['invite_code' => $invite_code]);
        if ($result && $result['code'] == 0) {
            return $result['data']['invitation_link'];
        }
        write_applog('ERROR', '根据邀请码查询邀请链接失败，$invite_code=' . $invite_code
            . '，返回数据：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        return null;
    }

    /**
     * @name 根据用户ID和场景查询邀请链接
     * @desc scene不传时返回该用户所有场景的邀请链接列表
     * @param integer $uid 必选 用户ID
     * @param string $scene 可选 邀请场景，如register
     * @return array|null 失败返回null
     * 传scene时返回单个邀请链接数组（不存在返回null）：
     * {
     *   "id": 1,                  // int 邀请链接ID
     *   "uid": 123,               // int 邀请人用户ID
     *   "scene": "register",      // string 邀请场景
     *   "invite_code": "Ab3xYz",  // string 邀请码
     *   "cookie_ttl": 1296000,    // int cookie有效期，单位秒
     *   "remark": "推广活动",     // string 备注
     *   "ctime": 1494567890,      // int 创建时间戳
     *   "mtime": 1494567890       // int 最后修改时间戳
     * }
     * 不传scene时返回邀请链接数组列表（无数据返回空数组[]）：
     * [ {同上结构}, ... ]
     * @throws
     */
    public function find_invitation_link_by_uid($uid, $scene = '')
    {
        $params = ['uid' => $uid];
        if ($scene !== '') {
            $params['scene'] = $scene;
        }
        $result = $this->_curl_post('/internal/find_invitation_link_by_uid', $params);
        if ($result && $result['code'] == 0) {
            if ($scene !== '') {
                return isset($result['data']['invitation_link']) ? $result['data']['invitation_link'] : null;
            }
            return isset($result['data']['invitation_links']) ? $result['data']['invitation_links'] : [];
        }
        write_applog('ERROR', '根据用户ID查询邀请链接失败，$uid=' . $uid . '，$scene=' . $scene
            . '，返回数据：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        return null;
    }

    /**
     * @name 创建邀请链接
     * @desc 邀请码由用户中心自动生成，若该用户在该场景下已有邀请链接则更新cookie_ttl和remark
     * @param integer $uid 必选 用户ID
     * @param string $scene 必选 邀请场景，如register，只能包含小写字母、数字、下划线
     * @param string $remark 可选 备注
     * @param integer $cookie_ttl 可选 cookie有效期，单位秒，默认15天(1296000)
     * @return array|null 成功返回邀请链接数组，失败返回null
     * 成功返回示例：
     * {
     *   "id": 1,                  // int 邀请链接ID
     *   "uid": 123,               // int 邀请人用户ID
     *   "scene": "register",      // string 邀请场景
     *   "invite_code": "Ab3xYz",  // string 邀请码（自动生成）
     *   "cookie_ttl": 1296000,    // int cookie有效期，单位秒
     *   "remark": "推广活动",     // string 备注
     *   "ctime": 1494567890,      // int 创建时间戳
     *   "mtime": 1494567890       // int 最后修改时间戳
     * }
     * @throws
     */
    public function create_invitation_link($uid, $scene, $remark = '', $cookie_ttl = 0)
    {
        $params = [
            'uid' => $uid,
            'scene' => $scene,
            'remark' => $remark,
        ];
        if ($cookie_ttl > 0) {
            $params['cookie_ttl'] = $cookie_ttl;
        }
        $result = $this->_curl_post('/internal/create_invitation_link', $params);
        if ($result && $result['code'] == 0) {
            return $result['data']['invitation_link'];
        }
        write_applog('ERROR', '创建邀请链接失败，$uid=' . $uid . '，$scene=' . $scene
            . '，返回数据：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        return null;
    }

    /**
     * @name 退出登录
     * @desc 请求用户中心退出当前登录用户
     * @return boolean|null 成功返回true，失败返回false，未登录返回true，缺少vk返回null
     * @throws
     */
    public function sign_out(){
        if(!$user_info = $this->get_current_user_info()){
            return true;
        }
        if (empty($_COOKIE['vk'])){
            return null;
        }
        else {
            redis_y::I()->del(REDIS_LOGIN_USER_INFO . $_COOKIE['vk']);
            redis_y::I()->del(REDIS_LAST_LOGIN_UID . $_COOKIE['vk']);
        }
        $result = $this->_curl_post('/internal/sign_out', ['uid'=>$user_info['uid']]);
        if ($result && $result['code']==0){
            return true;
        }
        else{
            write_applog('ERROR', '向用户中心请求用户退出登录时失败，$user_info='
                .json_encode($user_info, JSON_UNESCAPED_UNICODE)
                .'，返回数据：'.json_encode($result, JSON_UNESCAPED_UNICODE));
            return false;
        }
    }

    /**
     * @name 检查用户权限
     * @desc 判断用户是否拥有某项权限
     * @param integer $uid 必选 用户ID
     * @param string $permission_key 必选 权限键名
     * @return boolean true有权限，false无权限
     * @throws
     */
    public function check_user_permission($uid, $permission_key){
        $result = $this->_curl_post('/internal/check_user_permission', [
            'uid'=>$uid,
            'permission_key'=>$permission_key
        ]);
        if ($result && $result['code']==0){
            return empty($result['data']['result'])?false:true;
        }
        else{
            write_applog('ERROR', '向用户中心检查用户是否拥有某项权限时失败，$uid='
                .$uid.'，$permission_key='.$permission_key
                .'，返回数据：'.json_encode($result, JSON_UNESCAPED_UNICODE));
            return false;
        }
    }

    /**
     * @name 创建权限
     * @desc 在用户中心创建权限，并默认授予指定用户。权限键名只能包含字母、数字、下划线，长度3-25，不能以grant_开头
     * @param integer $uid 必选 用户ID，创建后默认授予此用户该权限
     * @param string $permission_key 必选 权限键名，3-25个字符
     * @param string $permission_name 必选 权限名称，1-40个字符
     * @param string $description 可选 权限描述，最长200个字符
     * @return array|null 返回用户中心接口返回的完整结果，失败或网络异常返回null
     * 成功返回示例：
     * {
     *   "code": 0,          // 0保存成功，6键名格式错误，7键名不能以grant_开头，8保存失败，11键名已存在
     *   "msg": "保存成功",
     *   "data": []
     * }
     * @throws
     */
    public function insert_permission($uid, $permission_key, $permission_name, $description=''){
        return $this->_curl_post('/internal/insert_permission', [
            'uid'=>$uid,
            'permission_key'=>$permission_key,
            'permission_name'=>$permission_name,
            'description'=>$description,
        ]);
    }

    /**
     * @name 删除权限
     * @desc 根据权限键名删除权限，同时清除相关用户的权限缓存
     * @param string $permission_key 必选 权限键名
     * @return array|null 返回用户中心接口返回的完整结果，失败或网络异常返回null
     * 成功返回示例：
     * {
     *   "code": 0,          // 0删除成功，6键名格式错误，7键名不能以grant_开头
     *   "msg": "删除成功",
     *   "data": []
     * }
     * @throws
     */
    public function delete_permission_by_key($permission_key){
        return $this->_curl_post('/internal/delete_permission_by_key', ['permission_key'=>$permission_key]);
    }

    /**
     * @name 给用户授权
     * @desc 给指定用户授予指定权限，若用户已拥有该权限则直接返回成功
     * @param integer $uid 必选 被授权人的用户ID
     * @param string $permission_key 必选 权限键名，不含app_id前缀
     * @return array|null 返回用户中心接口返回的完整结果，失败或网络异常返回null
     * 成功返回示例：
     * {
     *   "code": 0,          // 0保存成功，1保存失败，11权限不存在，12用户不存在
     *   "msg": "保存成功",
     *   "data": []
     * }
     * @throws
     */
    public function grant_permission($uid, $permission_key){
        return $this->_curl_post('/internal/grant_permission', [
            'uid'=>$uid,
            'permission_key'=>$permission_key,
        ]);
    }

    /**
     * @name 获取当前用户的信息，可用于判断当前用户是否登录
     * @desc 优先读本地Redis缓存，缓存未命中时请求用户中心接口，不直接读数据库
     * @return array|null 已登录返回用户基础信息数组，未登录或缺少vk返回null
     * 成功返回示例：
     * {
     *   "uid": 123,              // int/string 用户ID
     *   "nickname": "Jim",       // string 昵称
     *   "avatar": "https://...", // string 头像URL
     *   "gender": "male",        // string 性别 male/female
     *   "last_active": 1514567890, // int 最后活跃时间戳
     *   "remember": 0            // int 是否记住登录 0/1
     * }
     * @throws
     */
    public function get_current_user_info()
    {
        if (empty($_COOKIE['vk'])){
            return null;
        }
        //读本地缓存
        if($user_info = redis_y::I()->hgetall(REDIS_LOGIN_USER_INFO.$_COOKIE['vk'])){
            return $user_info;
        }
        if(!$uid = redis_y::I()->get(REDIS_LAST_LOGIN_UID.$_COOKIE['vk'])){
            //使用vk查询用户中心
            if($user_info = $this->check_login_by_vk($_COOKIE['vk']) ) {
                if ($user_info['code'] == 0) {
                    //缓存用户登录的信息
                    redis_y::I()->hmset(REDIS_LOGIN_USER_INFO . $_COOKIE['vk'], $user_info['data']['user_info']);
                    redis_y::I()->expire(REDIS_LOGIN_USER_INFO . $_COOKIE['vk'], TIME_30_SEC);
                    return $user_info['data']['user_info'];
                }
            }
        }
        //使用uid查询用户中心
        if($uid && $user_info = $this->check_login_by_uid($uid) ){
            if ($user_info['code']==0){
                //缓存用户登录的信息
                redis_y::I()->hmset(REDIS_LOGIN_USER_INFO.$_COOKIE['vk'], $user_info['data']['user_info']);
                redis_y::I()->expire(REDIS_LOGIN_USER_INFO.$_COOKIE['vk'], TIME_30_SEC);
                return $user_info['data']['user_info'];
            }
            else{
                write_applog('NOTICE', '用UID向用户中心查询用户是否登录时失败，$uid='.$uid
                    .'，返回数据：'.json_encode($user_info, JSON_UNESCAPED_UNICODE));
                return null;
            }
        }
        else {
            return null;
        }
    }

    /**
     * @name 发送POST请求到用户中心
     * @desc 自动附带dtype、time、app_id、lang、sign等公共参数
     * @param string $uri 必选 接口路径
     * @param array $params 可选 业务参数
     * @param array $headers 可选 请求头
     * @return array|null 返回解码后的JSON结果
     */
    private function _curl_post($uri, $params=[], $headers=[]){
        global $config;
        $params = array_merge($params, [
            'dtype' => 'json',
            'time' => time(),
            'app_id' => $config['user_center']['app_id'],
            'lang' => $config['lang'],
        ]);
        $params['sign'] = $this->_create_sign($params);
        $url = $config['user_center']['host'].$uri;
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );
        $result = curl_exec ( $ch );
        curl_close ( $ch );
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * @name 发送GET请求
     * @desc 预留的GET请求方法
     * @param string $url 必选 请求地址
     * @param array $params 可选 请求参数
     * @return string
     */
    private function _curl_get($url, $params){
        $url = $url.'?' . http_build_query( $params );
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
        $result = curl_exec ( $ch );
        curl_close ( $ch );

        return $result;
    }

    /**
     * @name 生成签名
     * @desc 用于调用用户中心内部接口
     * @param array $params 必选 请求参数
     * @return string
     */
    private function _create_sign($params)
    {
        global $config;
        $query_string = $this->_params_to_query_string($params);
        return md5($config['user_center']['app_id'].md5($query_string).$config['user_center']['app_secret']);
    }

    /**
     * @name 参数转query字符串
     * @desc 按键名排序后拼接成key=value格式，用于签名
     * @param array $params 必选 请求参数
     * @return string
     */
    private function _params_to_query_string($params)
    {
        ksort($params);
        $arr = [];
        foreach ($params as $key => $param){
            $arr[] = $key.'='.$param;
        }
        unset($params, $key, $param);
        return implode('&', $arr);
    }
}
