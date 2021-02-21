<?php
/*
 * Linkedin授权登录
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 22:19
 */

class oauth_linkedin extends oauth
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $get_request_code_url = 'https://www.linkedin.com/oauth/v2/authorization';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $get_access_token_url = 'https://www.linkedin.com/oauth/v2/accessToken';

    /**
     * 获取request_code的额外参数,可在配置中修改 URL查询字符串格式
     * @var srting
     */
    protected $authorize = 'scope=r_basicprofile';

    /**
     * API根路径
     * @var string
     */
    protected $api_base = 'https://www.linkedin.com/';

    /**
     * 调用接口类型,如:qq/wechat/wechat_open/linkedin
     * @var string
     */
    protected $type = 'linkedin';

    /**
     * 获取当前授权应用的openid
     * @return string
     */
    public function openid(){
        //无需要单独获得openid
    }

    /**
     * 跳转过去授权登录
     * @param string $scope 应用授权作用域，r_emailaddress、r_basicprofile、r_liteprofile
     */
    public function login($scope='r_basicprofile', $state=null){
        if(!$state) {
            //-------生成唯一随机串防CSRF攻击
            $state = md5(uniqid(rand(), TRUE).microtime().uniqid());
        }
        $this->write_session('state', $state);

        //-------构造请求参数列表
        $keysArr = array(
            "response_type" => "code",
            "client_id" => $this->app_key,
            "redirect_uri" => $this->callback,
            "state" => $state,
            "scope" => $scope
        );

        $login_url =  $this->combine_url($this->get_request_code_url, $keysArr);

        header('Location: '.$login_url);
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
     * 授权登录回来后，进行身份验证
     * 成功则返回个人信息
     */
    public function check_access_token(){
        $url = 'https://api.linkedin.com/v1/people/~';
        $header = [
            'Host: api.linkedin.com',
            'Connection: Keep-Alive',
            'Authorization: Bearer '.$this->access_token
        ];
        $result = $this->http($url, [], 'GET', 0, $header);
        /*
         * 成功返回
array(5) {
  ["id"]=>
  string(10) "CI265Mhdch"
  ["first-name"]=>
  string(3) "航"
  ["last-name"]=>
  string(3) "叶"
  ["headline"]=>
  string(42) "Paramida Tech Ltd - Director Of Operations"
  ["site-standard-profile-request"]=>
  array(1) {
    ["url"]=>
    string(136) "https://www.linkedin.com/profile/view?id=AAoAACsSTXEB0E0XK-iXqgvzG5ycsyNSPnu7QXE&authType=name&authToken=YbxM&trk=api*a5451705*s5764065*"
  }
}
         * 失败返回
array(5) {
  ["status"]=>
  string(3) "401"
  ["timestamp"]=>
  string(13) "1552893126286"
  ["request-id"]=>
  string(10) "I18KARKK5M"
  ["error-code"]=>
  string(1) "0"
  ["message"]=>
  string(21) "Invalid access token."
}
         */
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
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

    /**
     * _call
     * 魔术方法，做api调用转发
     * @param string $name    调用的方法名称
     * @param array $arg      参数列表数组
     * @since 5.0
     * @return array          返加调用结果数组
     */
    public function __call($name,$arg){
    }
}