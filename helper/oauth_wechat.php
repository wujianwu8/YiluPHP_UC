<?php
/*
 * 微信公众平台（服务号）授权登录
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 16:15
 */

class oauth_wechat extends oauth
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $get_request_code_url = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    /**
     * 获取requestCode的api接口（开放平台扫码使用）
     * @var string
     */
    protected $get_request_code_qr_url = 'https://open.weixin.qq.com/connect/qrconnect';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $get_access_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * 获取request_code的额外参数,可在配置中修改 URL查询字符串格式
     * @var srting
     */
    protected $authorize = 'scope=snsapi_userinfo,snsapi_base';

    /**
     * API根路径
     * @var string
     */
    protected $api_base = 'https://api.weixin.qq.com/';

    /**
     * 调用接口类型,如:qq/wechat/wechat_open
     * @var string
     */
    protected $type = 'wechat';


    //定义可用接口
    /*
     * 加#表示非必须，无则不传入url(url中不会出现该参数)， "key" => "val" 表示key如果没有定义则使用默认值val
     * 规则 array( baseUrl, argListArr, method)
     */
    protected $api_map = array(
        //刷新access_token
        "refresh_token" => array(
            "https://api.weixin.qq.com/sns/oauth2/refresh_token",
            array("grant_type"=>"refresh_token", "refresh_token"),
            "GET"
        ),
        //拉取用户信息
        "userinfo" => array(
            "https://api.weixin.qq.com/sns/userinfo",
            array("lang"=>"zh_CN"),
            "GET"
        ),
        //检验授权凭证（access_token）是否有效
        "check_access_token" => array(
            "https://api.weixin.qq.com/sns/auth",
            array("access_token","openid"),
            "GET"
        ),
    );

    /**
     * 获取当前授权应用的openid
     * @return string
     */
    public function openid(){
        if($this->openid) {
            $openid = $this->openid;
            return $openid;
        }
        else if($this->access_token){
            $data = $this->http($this->url('oauth2.0/me'), array('access_token' => $this->access_token));
            $data = json_decode(trim(substr($data, 9), " );\n"), true);
            if(isset($data['openid'])) {
                $openid = $data['openid'];
                unset($data);
                return $openid;
            }
            else{
                $desc = $data['error_description'];
                unset($data);
                throw new Exception('获取用户openid出错：'.$desc);
            }
        } else {
            throw new Exception('没有获取到openid！');
        }
    }

    /**
     * 跳转过去授权登录
     * @param string $scope 应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），
     *                      snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。
     *                          并且，即使在未关注的情况下，只要用户授权，也能获取其信息 ）
     *                      snsapi_login 微信开放平台扫码登录时使用
     * @param string $callback 回调地址，默认为配置中的回调地址
     */
    public function login($scope='snsapi_userinfo', $state=null, $callback=null){
        if(!$state) {
            //-------生成唯一随机串防CSRF攻击
            $state = md5(uniqid(rand(), TRUE).microtime().uniqid());
        }
        $this->write_session('state', $state);

        if ($scope=='snsapi_login'){
            $this->load_config('wechat_open');

            if(!$callback){
                $callback = $this->callback;
            }
            //-------构造请求参数列表
            $keysArr = array(
                "response_type" => 'code',
                "appid" => $this->app_key,
                "redirect_uri" => urlencode($callback),
                "state" => $state,
                "scope" => $scope,
            );
            $login_url = $this->combine_url($this->get_request_code_qr_url, $keysArr);
        }
        else {
            $this->load_config('wechat');

            if(!$callback){
                $callback = $this->callback;
            }
            //-------构造请求参数列表
            $keysArr = array(
                "response_type" => "code",
                "appid" => $this->app_key,
                "redirect_uri" => urlencode($callback),
                "state" => $state,
                "scope" => $scope
            );
            $login_url = $this->combine_url($this->get_request_code_url, $keysArr);
        }

        header("Location:$login_url".'#wechat_redirect');
        exit;
    }

    /**
     * 授权登录回来后，检查参数的有效性
     */
    public function check_callback(){
        $state = $this->read_session("state");
        $get_state = input::I()->get_trim('state', '');
        //--------验证state防止CSRF攻击
        if(!$state || $get_state != $state){
            throw new validate_exception('The state does not match. You may be a victim of CSRF.', CODE_ATTACKED_BY_CSRF);
        }
        return true;
    }

    /**
     * combineURL
     * 拼接url
     * @param string $baseURL   基于的url
     * @param array  $keysArr   参数列表数组
     * @return string           返回拼接的url
     */
    public function combine_url($baseURL,$keysArr){
        $combined = $baseURL."?";
        $valueArr = array();

        foreach($keysArr as $key => $val){
            $valueArr[] = "$key=$val";
        }

        $keyStr = implode("&",$valueArr);
        $combined .= ($keyStr);

        return $combined;
    }

    /**
     * 解析access_token方法请求后的返回值
     * @param string $result 获取access_token的方法的返回值
     */
    protected function parse_token($result, $extend){
        return json_decode($result, true);
    }

    //调用相应api
    private function _applyAPI($arr, $argsList, $baseUrl, $method){
        $pre = "#";
        $keysArr = array(
            "access_token" => $this->access_token,
            "openid" => $this->openid
        );

        $optionArgList = array();//一些多项选填参数必选一的情形
        foreach($argsList as $key => $val){
            $tmpKey = $key;
            $tmpVal = $val;

            if(!is_string($key)){
                $tmpKey = $val;

                if(strpos($val,$pre) === 0){
                    $tmpVal = $pre;
                    $tmpKey = substr($tmpKey,1);
                    if(preg_match("/-(\d$)/", $tmpKey, $res)){
                        $tmpKey = str_replace($res[0], "", $tmpKey);
                        $optionArgList[$res[1]][] = $tmpKey;
                    }
                }else{
                    $tmpVal = null;
                }
            }

            //-----如果没有设置相应的参数
            if(!isset($arr[$tmpKey]) || $arr[$tmpKey] === ""){

                if($tmpVal == $pre){//则使用默认的值
                    continue;
                }else if($tmpVal){
                    $arr[$tmpKey] = $tmpVal;
                }else{
                    if($v = $_FILES[$tmpKey]){

                        $filename = dirname($v['tmp_name'])."/".$v['name'];
                        move_uploaded_file($v['tmp_name'], $filename);
                        $arr[$tmpKey] = "@$filename";

                    }else{
                        throw new Exception('api调用参数错误，未传入参数'.$tmpKey,CODE_SYSTEM_ERR);
                    }
                }
            }

            $keysArr[$tmpKey] = $arr[$tmpKey];
        }
        //检查选填参数必填一的情形
        foreach($optionArgList as $val){
            $n = 0;
            foreach($val as $v){
                if(in_array($v, array_keys($keysArr))){
                    $n ++;
                }
            }

            if(! $n){
                $str = implode(",",$val);
                throw new Exception('api调用参数错误 '.$str.'必填一个',CODE_SYSTEM_ERR);
            }
        }

        if($method == "POST"){
            $response = $this->http($baseUrl, $keysArr, 'POST');
        }else if($method == "GET"){
            $response = $this->http($baseUrl, $keysArr, 'GET');
        }

        return $response;
    }

    /**
     * _call
     * 魔术方法，做api调用转发
     * @param string $name    调用的方法名称
     * @param array $arg      参数列表数组
     * @since 5.0
     * @return array          返加调用结果数组
     */
    public function __call($name,$arg){
        //检查是否有access token
        if(empty($this->access_token)){
            throw new Exception('调用API前未设置access token',CODE_SYSTEM_ERR);
        }
        //检查是否有openid
        if(empty($this->openid)){
            throw new Exception('调用API前未设置openid',CODE_SYSTEM_ERR);
        }
        //如果api_map不存在相应的api
        if(empty($this->api_map[$name])){
            throw new Exception('api调用名称错误，不存在的API：'.$name,CODE_SYSTEM_ERR);
        }

        //从api_map获取api相应参数
        $baseUrl = $this->api_map[$name][0];
        $argsList = $this->api_map[$name][1];
        $method = isset($this->api_map[$name][2]) ? $this->api_map[$name][2] : "GET";

        if(empty($arg)){
            $arg[0] = null;
        }
        $responseArr = json_decode($this->_applyAPI($arg[0], $argsList, $baseUrl, $method), true);

        //检查返回ret判断api是否成功调用
        if(!empty($responseArr['openid'])){
            return $responseArr;
        }else{
            throw new Exception($responseArr['errmsg'],$responseArr['errcode']);
        }

    }
}