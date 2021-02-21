<?php
/*
 * 阿里云授权登录
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 15:30
 */

class oauth_alipay extends base_class
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $get_request_code_url = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm';

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
     * @param string $scope 应用授权作用域，auth_user （用户头像、昵称、用户ID、省市），auth_userinfo （获取不到头像、昵称、省市 ）
     * @param string $callback 回调地址，默认为配置中的回调地址
     */
    public function login($scope='auth_user', $callback=null){
        $config = $GLOBALS['config']['oauth_plat']['alipay'];
        $appid = $config['app_id'];
        if(!$callback){
            $callback = $config['callback'];
        }

        header("Location:".$this->combine_url($this->get_request_code_url, [
                'app_id' => $appid,
                'scope' => $scope,
                'redirect_uri' => urlencode ($callback),
            ]));
        exit;
    }

    /**
     * 获取aop实例
     */
    private function _get_aop(){
        include_once 'alipay/AopSdk.php';
        include_once 'alipay/aop/AopClient.php';
        include_once 'alipay/aop/request/AlipaySystemOauthTokenRequest.php';
        include_once 'alipay/aop/request/AlipayUserUserinfoShareRequest.php';
        include_once 'alipay/aop/request/AlipaySystemOauthTokenRequest.php';
        include_once 'alipay/aop/request/AlipayUserInfoShareRequest.php';
        $config = $GLOBALS['config']['oauth_plat']['alipay'];

        //初始化
        $aop = new AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $config['app_id'];
        $aop->rsaPrivateKey = $config['rsa_private_key'];   //私钥  文件名（rsa_private_key.pem）
        $aop->alipayrsaPublicKey = $config['alipay_rsa_public_key'];   //公钥  文件名 （rsa_public_key.pem）
        $aop->encryptKey = $config['app_id'];
        $aop->apiVersion = '1.0';
        $aop->signType = $config['sign_type'];
        $aop->postCharset='UTF-8';
        $aop->format='json';
        unset($config);
        return $aop;
    }

    /**
     * 授权登录回来后，使用auth_code换取access_token
     * @param string $auth_code
     */
    public function get_access_token($auth_code){
        $aop = $this->_get_aop();

        //获取access_token
        $request = new AlipaySystemOauthTokenRequest ();
        $request->setGrantType("authorization_code");
        //这里传入 code
        $request->setCode($auth_code);

        $result = $aop->execute($request);
        if (!empty($result->error_response)){
            throw new validate_exception($result->error_response->sub_msg, $result->error_response->code);
        }
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $access_token = $result->$responseNode;
        unset($auth_code, $aop, $request, $result, $responseNode);
        /* *
        object(stdClass)#16 (6) {
          ["access_token"]=> string(40) "authusrBa3e124c841983j47881d662360e9fX75"
          ["alipay_user_id"]=> string(32) "20881072459123186845340412347575"
          ["expires_in"]=> int(1296000)
          ["re_expires_in"]=> int(2592000)
          ["refresh_token"]=> string(40) "authusrB0b7489376f9d420cb4008c210bfbdX75"
          ["user_id"]=> string(16) "2088102598764752"
        }
         * */
        return $access_token;
    }

    /**
     * 通过access_token获取用户信息
     * @param string $access_token
     * @param string $scope 应用授权作用域，auth_user （用户头像、昵称、用户ID、省市），auth_userinfo （获取不到头像、昵称、省市 ）
     */
    public function get_user_info($access_token, $scope='auth_user'){
        $aop = $this->_get_aop();

        //获取用户信息
        if ($scope=='auth_userinfo') {
            //授权时scope为auth_userinfo调用此类（获取不到头像、昵称、省市 ）
            /* *
             object(stdClass)#20 (2) {
              ["alipay_user_userinfo_share_response"]=>
              object(stdClass)#19 (11) {
                ["user_status"]=> string(1) "T"
                ["is_mobile_auth"]=> string(1) "T"
                ["is_certified"]=> string(1) "T"
                ["is_licence_auth"]=> string(1) "F"
                ["user_id"]=> string(32) "20881072459123186845340412347575"
                ["is_certify_grade_a"]=> string(1) "T"
                ["is_student_certified"]=> string(1) "F"
                ["user_type_value"]=> string(1) "2"
                ["is_bank_auth"]=> string(1) "T"
                ["is_id_auth"]=> string(1) "T"
                ["alipay_user_id"]=> string(16) "2088102598764752"
              }
              ["sign"]=> string(344) "RsRdxkB...0sQOWPKXTQXQ=="
            }
             * */
            $request_a = new AlipayUserUserinfoShareRequest ();
        }
        else if ($scope=='auth_user') {
            //授权时scope为auth_user （用户头像、昵称、用户ID、省市）
            /* *
                object(stdClass)#20 (2) {
                  ["alipay_user_info_share_response"]=>
                  object(stdClass)#19 (12) {
                    ["code"]=>string(5) "10000"
                    ["msg"]=> string(7) "Success"
                    ["avatar"]=> string(63) "https://tfs.alipayobjects.com/images/partner/T11TxuXkJXXXXXXXXX"
                    ["city"]=> string(9) "深圳市"
                    ["gender"]=> string(1) "m"
                    ["is_certified"]=> string(1) "T"
                    ["is_student_certified"]=> string(1) "F"
                    ["nick_name"]=> string(9) "Bambooner"
                    ["province"]=> string(9) "广东省"
                    ["user_id"]=> string(16) "2088102598764752"
                    ["user_status"]=> string(1) "T"
                    ["user_type"]=> string(1) "2"
                  }
                  ["sign"]=> string(344) "PbTl0q4wWlk...gtZ0wxISEPXNItwQ=="
                }
             * */
            $request_a = new AlipayUserInfoShareRequest();
        }

        //这里传入获取的access_token
        $result_a = $aop->execute ($request_a,$access_token);
        $responseNode_a = str_replace(".", "_", $request_a->getApiMethodName()) . "_response";

        $user_info = $result_a->$responseNode_a;
        unset($aop, $scope, $request_a, $responseNode_a);
        return $user_info;
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

}