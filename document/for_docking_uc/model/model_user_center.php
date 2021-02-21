<?php
/*
 * 与用户中心系统对接的模型类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * Date: 2021/01/21
 * Time: 22:33
 */

class model_user_center extends base_class
{

    /**
     * @name 获取当前用户的信息，可用于判断当前用户是否登录
     * @desc 不读数据库，只读存在SESSION中的基本信息
     * @param string vk vk值 必选 用户cookie中的vk的值
     * @param integer keep_alive 是否保活 可选 0不延长用户登录时效，1为延长用户登录时效，默认为不延长
     * @return array/null
     * @throws
     */
    public function check_login_by_vk($vk, $keep_alive=1){
        return $this->_curl_post('/internal/check_login_by_vk', ['vk'=>$vk,'keep_alive'=>$keep_alive]);
    }

    public function check_login_by_uid($uid, $keep_alive=1){
        return $this->_curl_post('/internal/check_login_by_uid', ['uid'=>$uid,'keep_alive'=>$keep_alive]);
    }

    public function check_login_by_tlt($tlt){
        return $this->_curl_post('/internal/check_login_by_tlt', ['tlt'=>$tlt]);
    }

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

    public function find_uid_by_username($username){
        return $this->_curl_post('/internal/find_uid_by_username', ['username'=>$username]);
    }

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

    public function insert_permission($uid, $permission_key, $permission_name, $description=''){
        return $this->_curl_post('/internal/insert_permission', [
            'uid'=>$uid,
            'permission_key'=>$permission_key,
            'permission_name'=>$permission_name,
            'description'=>$description,
        ]);
    }

    public function delete_permission_by_key($permission_key){
        return $this->_curl_post('/internal/delete_permission_by_key', ['permission_key'=>$permission_key]);
    }

    /**
     * @name 获取当前用户的信息，可用于判断当前用户是否登录
     * @desc 不读数据库，只读存在SESSION中的基本信息
     * @return array/null
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
        if($user_info = $this->check_login_by_uid($uid) ){
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

    private function _curl_post($uri, $params=[], $headers=[]){
//        $headers=array(
//
//            "Content-Type: application/x-www-form-urlencoded",
//            "Timestamp: " . $timestamp,
//            "App-Key: " . $appKey,
//            "Nonce: " . $nonce,
//            "Signature:".$local_signature,
//            'Content-Length: ' . strlen($postData)
//        );
        global $config;
        $params = array_merge($params, [
            'dtype' => 'json',
            'time' => time(),
            'app_id' => $config['user_center']['app_id'],
            'lang' => $config['user_center']['lang'],
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

    private function _create_sign($params)
    {
        global $config;
        $query_string = $this->_params_to_query_string($params);
        return md5($config['user_center']['app_id'].md5($query_string).$config['user_center']['app_secret']);
    }

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
