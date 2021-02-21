<?php
/*
 * QQ授权登录
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 16:15
 */

class oauth_qq extends oauth
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $get_request_code_url = 'https://graph.qq.com/oauth2.0/authorize';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $get_access_token_url = 'https://graph.qq.com/oauth2.0/token';

    /**
     * 获取request_code的额外参数,可在配置中修改 URL查询字符串格式
     * @var srting
     */
    protected $authorize = 'scope=get_user_info,add_share';

    /**
     * API根路径
     * @var string
     */
    protected $api_base = 'https://graph.qq.com/';

    /**
     * 调用接口类型,如:qq/wechat/wechat_open
     * @var string
     */
    protected $type = 'qq';


    //定义可用接口
    /*
     * 加#表示非必须，无则不传入url(url中不会出现该参数)， "key" => "val" 表示key如果没有定义则使用默认值val
     * 规则 array( baseUrl, argListArr, method)
     */
    protected $api_map = array(
        /*                       qzone                    */
        "add_blog" => array(
            "https://graph.qq.com/blog/add_one_blog",
            array("title", "format" => "json", "content" => null),
            "POST"
        ),
        "add_topic" => array(
            "https://graph.qq.com/shuoshuo/add_topic",
            array("richtype","richval","con","#lbs_nm","#lbs_x","#lbs_y","format" => "json", "#third_source"),
            "POST"
        ),
        "get_user_info" => array(
            "https://graph.qq.com/user/get_user_info",
            array("format" => "json"),
            "GET"
        ),
        "add_one_blog" => array(
            "https://graph.qq.com/blog/add_one_blog",
            array("title", "content", "format" => "json"),
            "GET"
        ),
        "add_album" => array(
            "https://graph.qq.com/photo/add_album",
            array("albumname", "#albumdesc", "#priv", "format" => "json"),
            "POST"
        ),
        "upload_pic" => array(
            "https://graph.qq.com/photo/upload_pic",
            array("picture", "#photodesc", "#title", "#albumid", "#mobile", "#x", "#y", "#needfeed", "#successnum", "#picnum", "format" => "json"),
            "POST"
        ),
        "list_album" => array(
            "https://graph.qq.com/photo/list_album",
            array("format" => "json")
        ),
        "add_share" => array(
            "https://graph.qq.com/share/add_share",
            array("title", "url", "#comment","#summary","#images","format" => "json","#type","#playurl","#nswb","site","fromurl"),
            "POST"
        ),
        "check_page_fans" => array(
            "https://graph.qq.com/user/check_page_fans",
            array("page_id" => "314416946","format" => "json")
        ),
        /*                    wblog                             */

        "add_t" => array(
            "https://graph.qq.com/t/add_t",
            array("format" => "json", "content","#clientip","#longitude","#compatibleflag"),
            "POST"
        ),
        "add_pic_t" => array(
            "https://graph.qq.com/t/add_pic_t",
            array("content", "pic", "format" => "json", "#clientip", "#longitude", "#latitude", "#syncflag", "#compatiblefalg"),
            "POST"
        ),
        "del_t" => array(
            "https://graph.qq.com/t/del_t",
            array("id", "format" => "json"),
            "POST"
        ),
        "get_repost_list" => array(
            "https://graph.qq.com/t/get_repost_list",
            array("flag", "rootid", "pageflag", "pagetime", "reqnum", "twitterid", "format" => "json")
        ),
        "get_info" => array(
            "https://graph.qq.com/user/get_info",
            array("format" => "json")
        ),
        "get_other_info" => array(
            "https://graph.qq.com/user/get_other_info",
            array("format" => "json", "#name", "fopenid")
        ),
        "get_fanslist" => array(
            "https://graph.qq.com/relation/get_fanslist",
            array("format" => "json", "reqnum", "startindex", "#mode", "#install", "#sex")
        ),
        "get_idollist" => array(
            "https://graph.qq.com/relation/get_idollist",
            array("format" => "json", "reqnum", "startindex", "#mode", "#install")
        ),
        "add_idol" => array(
            "https://graph.qq.com/relation/add_idol",
            array("format" => "json", "#name-1", "#fopenids-1"),
            "POST"
        ),
        "del_idol" => array(
            "https://graph.qq.com/relation/del_idol",
            array("format" => "json", "#name-1", "#fopenid-1"),
            "POST"
        ),
        /*                           pay                          */

        "get_tenpay_addr" => array(
            "https://graph.qq.com/cft_info/get_tenpay_addr",
            array("ver" => 1,"limit" => 5,"offset" => 0,"format" => "json")
        )
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
     * @param string $callback 回调地址，默认为配置中的回调地址
     */
    public function login($callback=null){
        //-------生成唯一随机串防CSRF攻击
        $state = md5(uniqid(rand(), TRUE).microtime().uniqid());
        $this->write_session('state', $state);

        if(!$callback){
            $callback = $this->callback;
        }
        //-------构造请求参数列表
        $keysArr = array(
            "response_type" => "code",
            "client_id" => $this->app_key,
            "redirect_uri" => urlencode($callback),
            "state" => $state,
            "scope" => 'get_user_info'
        );

        $login_url =  $this->combine_url($this->get_request_code_url, $keysArr);

        header("Location:$login_url");
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
        parse_str($result, $data);
        if(!empty($data['access_token']) && !empty($data['expires_in'])){
            $this->access_token = $data['access_token'];
            $data['openid'] = $this->openid();
            unset($result, $extend);
            return $data;
        } else{
            unset($extend, $data);
            throw new Exception('获取腾讯QQ access token 出错：'.$result);
        }
    }

    //调用相应api
    private function _applyAPI($arr, $argsList, $baseUrl, $method){
        $pre = "#";
        $keysArr = array(
            "oauth_consumer_key" => (int)$this->app_key,
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
            if($baseUrl == "https://graph.qq.com/blog/add_one_blog"){
                $response = $this->http($baseUrl, $keysArr, 'POST', 1);
            }
            else{
                $response = $this->http($baseUrl, $keysArr, 'POST', 0);
            }
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
        if($responseArr['ret'] == 0){
            return $responseArr;
        }else{
            throw new Exception($responseArr['msg'],$responseArr['ret']);
        }

    }
}