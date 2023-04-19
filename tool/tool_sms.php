<?php
/*
 * 短信发送类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 21:56
 */

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class tool_sms
{
    //存储单例
    private static $_instance = null;

    /**
     * 获取单例
     * @return model|null 返回单例
     */
    public static function I(){
        if (!static::$_instance){
            return static::$_instance = new self();
        }
        return static::$_instance;
    }

	public function __construct()
	{
	    if (empty($GLOBALS['config']['sms'])){
            throw new validate_exception('未配置发送短信的平台', CODE_NOT_CONFIG_SMS_PLAT);
        }
	}

    /**
     * 根据推荐算法选择一个平台
     * @param $area_code
     * @param $mobile
     * @return string
     */
    private function _recommendOnePlat(&$area_code, &$mobile){
        $all_plat = array_keys($GLOBALS['config']['sms']);
        $ten_min_key = REDIS_KEY_PHONE_SMS_IN_TEN_MIN.'_'.$area_code.'_'.$mobile;
        //从10分钟内没有使用过的平台中选择
        foreach ($all_plat as $plat_name){
            //检查10分钟内是否使用过该平台
            if(redis_y::I()->hexists($ten_min_key, $plat_name)){
                continue;
            }
            $exists = redis_y::I()->exists($ten_min_key);
            redis_y::I()->hset($ten_min_key, $plat_name, 1);
            if (!$exists){
                redis_y::I()->expire($ten_min_key, TIME_10_MIN);
            }
            unset($all_plat, $ten_min_key, $exists);
            return $plat_name;
        }

        //如果还没有选择到平台，则删除10分钟内的记录
        redis_y::I()->del($ten_min_key);

        //从所有平台中选择第一个
        foreach ($all_plat as $plat_name){
            redis_y::I()->hset($ten_min_key, $plat_name, 1);
            redis_y::I()->expire($ten_min_key, TIME_10_MIN);
            unset($all_plat, $ten_min_key);
            return $plat_name;
        }
        return false;
    }

    /**
     * 发短信验证码
     * @param integer $area_code 手机区号
     * @param integer $mobile 手机号，不包含区号
     * @param string $message 验证码的文本内容或者阿里云的模板CODE
     * @param string $template_code 短信模板CODE，云片不用，阿里云用
     * @param string $sign_name 短信使用到的签名，云片不用，阿里云用
     * @param array $template_param 短信中各变量的值，云片不用，阿里云用
     * @return bool
     */
    public function send_verify_code($area_code, $mobile, $message, $template_code=null, $sign_name=null, $template_param=[])
    {
        $area_code = intval($area_code);

        //判断是否含有连续6位一样的数字，如果有则是测试用户，将短信内容发送到企业微信里
        $is_tester = false;
        for ($i = 0; $i < 10; $i++) {
            $str = str_pad('', 6, $i);
            if (strpos($mobile, $str) > 0) {
                $is_tester = true;
                break;
            }
        }
        if ($is_tester && !empty($GLOBALS['config']['weixin_robot']['xiaomei'])){
            $message .= "\r\n手机号：{$area_code}-{$mobile}";
            tool_operate::I()->send_work_notice($message, $GLOBALS['config']['weixin_robot']['xiaomei']);
            return true;
        }

        $plat_name = $this->_recommendOnePlat($area_code, $mobile);
        $fun = 'send_sms_code_by_'.$plat_name;
        $res = $this->$fun($area_code, $mobile, $message, $template_code, $sign_name, $template_param);
        unset($area_code, $mobile, $message, $fun, $plat_name, $template_code, $sign_name, $template_param);
        return $res;
    }

    /**
     * 使用云信平台发短信验证码
     * @param integer $area_code 手机区号
     * @param integer $mobile 手机号，不包含区号
     * @param string $message 验证码的文本内容
     * @param string $template_code 短信模板CODE，使用云片用不上此参数
     * @param string $sign_name 短信使用到的签名，发给云片的签名直接写在短信内容中，所以使用云片用不上此参数
     * @param array $template_param 短信中各变量的值，使用云片用不上此参数
     * @return bool
     */
    public function send_sms_code_by_yun_pian(&$area_code, &$mobile, $message, $template_code=null, $sign_name=null, $template_param=[])
    {
        if($area_code===86){
            $phone_number = $mobile;
            $url = 'https://sms.yunpian.com/v2/sms/single_send.json';
        }
        else{
            $phone_number = '+'.$area_code.$mobile;
            $url = 'https://us.yunpian.com/v2/sms/single_send.json';
        }
        $param = [
            'apikey' => $GLOBALS['config']['sms']['yun_pian']['api_key'],
            'mobile' => $phone_number,
            'text' => $message,
        ];

        $time = time();
        $record = [
            'area_code' => $area_code,
            'mobile' => $mobile,
            'plat' => 'yun_pian',
            'client_ip' => client_ip(),
            'vk' => isset($_COOKIE['vk']) ? $_COOKIE['vk'] : '',
            'ctime' => $time,
            'mtime' => $time,
        ];
        curl::I()->setHeaders(
            ['Accept:application/json;charset=utf-8;', 'Content-Type:application/x-www-form-urlencoded;', 'charset=utf-8;']
        );
        $res = curl::I()->postJson($url, $param);
        $res = json_decode($res, true);
        unset($url, $message, $phone_number);
        if($res && $res['code']===0){
            $record['refuse_reason'] = json_encode($res);
            $record['is_send'] = 1;
            $record['mark'] = $res['sid'];
            insert_table($record, 'sms_record');
            unset($record, $time, $res, $param);
            return true;
        }
        else if($res) {
            //返回：{"http_status_code":400,"code":1,"msg":"请求参数缺失","detail":"参数 apikey 必须传入"}
            //{"http_status_code":400,"code":2,"msg":"请求参数格式错误","detail":"参数 mobile 格式不正确，mobile:8615502022241"}
            $record['refuse_reason'] = json_encode($res);
            model_sms_record::I()->insert_table($record);
            write_applog('ERROR', '调用云片发短信失败，$res：'.json_encode($res).'，param='.json_encode($param));
            unset($record, $time, $res, $param);
            return false;
        }
        else{
            write_applog('ERROR', '调用云片发短信失败，解析成数组失败');
            return false;
        };
    }

    /**
     * 使用阿里云发短信验证码
     * @param integer $area_code 手机区号
     * @param integer $mobile 手机号，不包含区号
     * @param string $message 验证码的文本内容，使用阿里云用不上此参数
     * @param string $template_code 短信模板CODE，阿里云上申请时产生的
     * @param string $sign_name 短信使用到的签名
     * @param array $template_param 短信中各变量的值
     * @return bool
     */
    public function send_sms_code_by_aliyun(&$area_code, &$mobile, $message, $template_code=null, $sign_name=null, $template_param=[])
    {
        if ($area_code === 86) {
            $phone_number = $mobile;
        } else {
            $phone_number = $area_code . $mobile;
        }

        /**
         *   PhoneNumbers
        接收短信的手机号码。
        格式：
        国内短信：11位手机号码，例如15951955195。
        国际/港澳台消息：国际区号+号码，例如85200000000。
        支持对多个手机号码发送短信，手机号码之间以英文逗号（,）分隔。上限为1000个手机号码。批量调用相对于单条调用及时性稍有延迟。
        验证码类型短信，建议使用单独发送的方式。

         *  SignName
        短信签名名称。请在控制台签名管理页面签名名称一列查看。
        必须是已添加、并通过审核的短信签名。

         *  TemplateCode
        短信模板ID。请在控制台模板管理页面模板CODE一列查看。
        必须是已添加、并通过审核的短信签名；且发送国际/港澳台消息时，请使用国际/港澳台短信模版。

         * TemplateParam
        短信模板变量对应的实际值，JSON格式。
        如果JSON中需要带换行符，请参照标准的JSON协议处理。
         *
         */
        AlibabaCloud::accessKeyClient($GLOBALS['config']['sms']['aliyun']['access_key_id'], $GLOBALS['config']['sms']['aliyun']['access_key_secret'])
            ->regionId($GLOBALS['config']['sms']['aliyun']['region_id']) // replace regionId as you need
            ->asDefaultClient();

        try {
            $query_array = [
                'RegionId' => "us-west-1",
                'PhoneNumbers' => $phone_number,
                'SignName' => $sign_name,
                'TemplateCode' => $template_code,
            ];

            //选填
            if(!empty($template_param)){
                $query_array['TemplateParam'] = json_encode($template_param);
            }
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => $query_array,
                ])
                ->request();
            $res = $result->toArray();
            if (empty($res['Code']) || $res['Code']!='OK'){
                write_applog('ERROR', '调用阿里云发短信失败，$res：'.json_encode($res,JSON_UNESCAPED_UNICODE).'，param='.json_encode($query_array,JSON_UNESCAPED_UNICODE));
            }
            unset($phone_number, $query_array, $result, $template_code, $sign_name, $template_param);
            return $res;
        } catch (ClientException $e) {
            write_applog('ERROR', '调用阿里云发短信时出错，客户端异常：'.$e->getErrorMessage().'，param='.json_encode($query_array,JSON_UNESCAPED_UNICODE));
            unset($phone_number, $query_array, $result, $template_code, $sign_name, $template_param);
            return false;
        } catch (ServerException $e) {
            write_applog('ERROR', '调用阿里云发短信时出错，服务器端异常：'.$e->getErrorMessage().'，param='.json_encode($query_array,JSON_UNESCAPED_UNICODE));
            unset($phone_number, $query_array, $result, $template_code, $sign_name, $template_param);
            return false;
        }
    }
}
