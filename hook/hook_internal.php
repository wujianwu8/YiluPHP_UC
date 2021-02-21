<?php
/*
 * 内部接口验证类
 */

class hook_internal extends hook
{
    public function run()
    {
    }

	public function __construct()
	{
	}

    public function check()
    {
//        return true;
        $params = input::I()->validate(
            [
                'sign' => 'required|trim|string|min:32|max:32|return',
                'time' => 'required|integer|min:1000000000|return',
                'app_id' => 'required|trim|string|max:20|return',
            ],
            [
                'sign.*' => 'sign参数错误',
                'time.*' => 'time参数错误',
                'app_id.*' => 'app_id参数错误',
            ],
            [
                'sign.*' => 1000,
                'time.*' => 1001,
                'app_id.*' => 1002,
            ]);
        //接口时效性验证，30秒内有效
        $diff_time = time()-$params['time'];
        if ($diff_time <= -30 ){
//            unset($diff_time, $params);
            throw new validate_exception('您的服务器时间太超前了'.$diff_time.'秒', 1003);
        }
        if ($diff_time >=30 ){
            unset($diff_time, $params);
            throw new validate_exception('您的服务器时间不准确或请求已经失效', 1004);
        }
        if(!$app_info = model_application::I()->find_table(['app_id'=>$params['app_id']], 'app_secret,app_white_ip,status')){
            unset($diff_time, $params, $app_info);
            throw new validate_exception('应用不存在', 1005);
        }
        unset($diff_time);
        if(empty($app_info['status'])){
            unset($params, $app_info);
            throw new validate_exception('应用不可用', 1006);
        }
        if(!empty($app_info['app_white_ip'])) {
            $white_ip = explode(',', $app_info['app_white_ip']);
            //IP白名单校验
            $client_ip = client_ip();
            if (!in_array($client_ip, $white_ip)){
                unset($params, $app_info, $white_ip, $client_ip);
                throw new validate_exception('IP白名单限制', 1007);
            }
            unset($white_ip, $client_ip);
        }
        //签名验证
        $all_params = $_REQUEST;
        unset($all_params['sign']);
        $query_string = $this->params_to_query_string($all_params);
        $sign = md5($params['app_id'].md5($query_string).$app_info['app_secret']);
        if ($params['sign']!==$sign){
            unset($params, $app_info, $all_params, $sign);
            throw new validate_exception('签名错误', 1008);
        }
        unset($params, $app_info, $all_params, $sign);
        return true;
    }

    public function params_to_query_string($params)
    {
        ksort($params);
        $arr = [];
        foreach ($params as $key => $param){
            $arr[] = $key.'='.$param;
        }
        unset($params, $key, $param);
        return implode('&', $arr);
    }

    public function __destruct()
    {
    }
}
