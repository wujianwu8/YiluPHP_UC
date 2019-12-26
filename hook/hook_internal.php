<?php
/*
 * 内部接口验证类
 */

class hook_internal
{
	public function __construct()
	{
	}

    public function check()
    {
//        return true;
        global $app;
        $params = $app->input->validate(
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
            unset($diff_time, $params);
            return_code(1003,'您的服务器时间太超前了');
        }
        if ($diff_time >=30 ){
            unset($diff_time, $params);
            return_code(1004,'您的服务器时间不准确或请求已经失效');
        }
        if(!$app_info = $app->model_application->find_table(['app_id'=>$params['app_id']], 'app_secret,app_white_ip,status')){
            unset($diff_time, $params, $app_info);
            return_code(1005,'应用不存在');
        }
        unset($diff_time);
        if(empty($app_info['status'])){
            unset($params, $app_info);
            return_code(1006,'应用不可用');
        }
        if(!empty($app_info['app_white_ip'])) {
            $white_ip = explode(',', $app_info['app_white_ip']);
            //IP白名单校验
            $client_ip = client_ip();
            if (!in_array($client_ip, $white_ip)){
                unset($params, $app_info, $white_ip, $client_ip);
                return_code(1007,'IP白名单限制');
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
            return_code(1008,'签名错误');
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
