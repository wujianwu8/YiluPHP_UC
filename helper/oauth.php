<?php
/*
 * 第三方登录平台
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 21:15
 */

abstract class oauth extends base_class
{
    /**
     * oauth版本
     * @var string
     */
    protected $version = '2.0';

    /**
     * 申请应用时分配的app_key
     * @var string
     */
    protected $app_key = '';

    /**
     * 申请应用时分配的 app_secret
     * @var string
     */
    protected $app_secret = '';

    /**
     * 授权类型 response_type 目前只能为code
     * @var string
     */
    protected $response_type = 'code';

    /**
     * grant_type 目前只能为 authorization_code
     * @var string
     */
    protected $grant_type = 'authorization_code';

    /**
     * 回调页面URL  可以通过配置文件配置
     * @var string
     */
    protected $callback = '';

    /**
     * 获取request_code的额外参数 URL查询字符串格式
     * @var srting
     */
    protected $authorize = '';

    /**
     * 获取request_code请求的URL
     * @var string
     */
    protected $get_request_code_url = '';

    /**
     * 获取access_token请求的URL
     * @var string
     */
    protected $get_access_token_url = '';

    /**
     * API根路径
     * @var string
     */
    protected $api_base = '';

    /**
     * 授权后获取到的TOKEN信息
     * @var array
     */
    protected $token = null;

    /**
     * 授权后获取到的access token信息
     * @var string
     */
    protected $access_token = null;

    /**
     * 授权后获取到的openid
     * @var string
     */
    protected $openid = null;

    /**
     * 调用接口类型,如:qq/wechat/wechat_open
     * @var string
     */
    protected $type = '';

    //初始化APIMap
    /*
     * 加#表示非必须，无则不传入url(url中不会出现该参数)， "key" => "val" 表示key如果没有定义则使用默认值val
     * 规则 array( baseUrl, argListArr, method)
     */
    protected $api_map = [];

    /**
     * 构造方法，配置应用信息
     * @param string $type 调用接口类型,如:qq/wechat/wechat_open
     * @return null
     */
    public function __construct($type=null){
        $this->load_config($this->type);
    }

    /**
     * 加载配置文件
     * @param string $type 调用接口类型,如:qq/wechat/wechat_open
     * @return null
     */
    public function load_config($type=null) {
        if (!$type){
            $type = $this->type;
        }
        if(empty($GLOBALS['config']['oauth_plat'][$type]['usable'])){
            throw new Exception('没有配置'.$type.'平台的信息或平台信息不可用', CODE_SYSTEM_ERR);
        }
        //获取应用配置
        $config = $GLOBALS['config']['oauth_plat'][$type];
        if(!empty($config['callback'])){
            $this->callback    = $config['callback'];
        }
        else{
            unset($config);
            throw new Exception('请配置回调页面地址！', CODE_SYSTEM_ERR);
        }
        if(!empty($config['authorize'])){
            $this->authorize    = $config['authorize'];
        }
        if(empty($config['app_key']) || empty($config['app_secret'])){
            unset($config);
            throw new Exception('请配置您申请的app_key和app_secret', CODE_SYSTEM_ERR);
        } else {
            $this->app_key    = $config['app_key'];
            $this->app_secret = $config['app_secret'];
            $this->callback = $config['callback'];
        }
        unset($config);
    }

    /**
     * 设置access_token
     * @param string $access_token
     * @return boolean
     */
    public function set_access_token($access_token) {
        $this->access_token = $access_token;
        return true;
    }

    /**
     * 设置openid
     * @param string $openid
     * @return boolean
     */
    public function set_openid($openid) {
        $this->openid = $openid;
        return true;
    }

    /**
     * 请求code
     */
    public function get_request_code_url(){
//        $this->config();
        //Oauth 标准参数
        $params = array(
            'client_id'     => $this->app_key,
            'redirect_uri'  => $this->callback,
            'response_type' => $this->response_type,
        );

        //获取额外参数
        if($this->authorize){
            parse_str($this->authorize, $_param);
            if(is_array($_param)){
                $params = array_merge($params, $_param);
            } else {
                unset($params);
                throw new Exception('authorize配置不正确！');
            }
        }
        return $this->get_request_code_url . '?' . http_build_query($params);
    }

    /**
     * 获取access_token
     * @param string $code 上一步请求到的code
     * @param string $extend
     * @return [
     * access_token: "4F3B47BDB096CF27AECA3AF98AF3ED62"
     * expires_in: "7776000"
     * refresh_token: "5F2D0AA872038DAE7FC35EE4C0C3EA31"
     * openid: "CD4C576D9E3BE2755E731D1A86BA0581"
     * ]
     */
    public function get_access_token($extend = null){
        $code = input::I()->get_trim('code', '');
        $params = array(
//            'client_id'     => $this->app_key,
//            'client_secret' => $this->app_secret,
            'grant_type'    => $this->grant_type,
            'code'          => $code,
//            'redirect_uri'  => $this->callback,
        );
        if (strpos($this->type, 'wechat')===0){
            $params['appid'] = $this->app_key;
            $params['secret'] = $this->app_secret;
        }
        else{
            $params['client_id'] = $this->app_key;
            $params['client_secret'] = $this->app_secret;
            $params['redirect_uri'] = $this->callback;
        }

        $data = $this->http($this->get_access_token_url, $params, 'POST');
        $token = $this->parse_token($data, $extend);
        unset($params, $data, $extend, $code);
        return $token;
    }

    /**
     * 合并默认参数和额外参数
     * @param array $params  默认参数
     * @param array/string $param 额外参数
     * @return array:
     */
    protected function param($params, $param){
        if(is_string($param))
            parse_str($param, $param);
        return array_merge($params, $param);
    }

    /**
     * 获取指定API请求的URL
     * @param  string $api API名称
     * @param  string $fix api后缀
     * @return string      请求的完整URL
     */
    protected function url($api, $fix = ''){
        return $this->api_base . $api . $fix;
    }

    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url    请求URL
     * @param  array  $params 请求参数
     * @param int $flag 标志位，设置为0 禁止 cURL 验证对等证书
     * @param  string $method 请求方法GET/POST
     * @param  array $header 请求方法GET/POST
     * @param  boolean $multi 请求方法GET/POST
     * @return array  $data   响应数据
     * @throws
     */
    protected function http($url, $params, $method = 'GET', $flag=0, $header = array(), $multi = false){
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
        );

        /* 根据请求类型设置特定参数 */
        switch(strtoupper($method)){
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new Exception('不支持的请求方式！');
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        if($method == 'POST' && !$flag){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($error){
            unset($url, $params, $method, $header, $multi, $opts, $ch);
            throw new Exception('请求发生错误：' . $error);
        }
        unset($url, $params, $method, $header, $multi, $opts, $ch, $error);
        return  $data;
    }

    /**
     * 把授权的相关信息存入session
     * @param string $key
     * @param string $value
     */
    public function write_session($key, $value){
        if(empty($_SESSION[$this->type.'_userData']) || !is_array($_SESSION[$this->type.'_userData'])){
            $data = array();
        }else{
            $data = $_SESSION[$this->type.'_userData'];
        }
        $data[$key] = $value;
        $_SESSION[$this->type.'_userData'] = $data;
    }

    /**
     * 从session中读取授权的相关信息
     * @param string $key
     * @return string/array
     */
    public function read_session($key=null){
        if(empty($_SESSION[$this->type.'_userData']) || !is_array($_SESSION[$this->type.'_userData'])){
            return null;
        }
        $data = $_SESSION[$this->type.'_userData'];
        if ($key===null){
            return $data;
        }
        return isset($data[$key]) ? $data[$key] : null;
    }

    /**
     * 抽象方法，在SNSSDK中实现
     * 跳转过去授权登录
     */
    abstract public function login();

    /**
     * 抽象方法，在SNSSDK中实现
     * 获取当前授权用户的SNS标识
     */
    abstract public function openid();

    /**
     * 抽象方法，在SNSSDK中实现
     * 解析access_token方法请求后的返回值
     */
    abstract protected function parse_token($result, $extend);

    /**
     * _call
     * 魔术方法，做api调用转发
     * @param string $name    调用的方法名称
     * @param array $arg      参数列表数组
     * @since 5.0
     * @return array          返加调用结果数组
     */
    abstract public function __call($name,$arg);

}