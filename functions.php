<?php
/*
 * 函数库，用户可在此添加自己所需函数
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 17/12/30
 * Time: 09:43
 */

/**
 * @name 获取当前完整的URL，包含http头和域名
 * @desc 会判断是HTTP还是HTTPS
 * @return string
 */
function get_url()
{
	$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	return $http_type . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * @name 获取客户端IP
 * @desc 获取客户端IP
 * @return string
 */
function client_ip(){
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    else{
        $ip = '';
    }
    return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
}

/**
 * @name 将10进制的数字转换成54进制
 * @desc
 * @return string
 */
function ten_to_54($int)
{
	$result = '';
	$step = 54;
	$str = '0123456789abcdefghijklmnopqrstuvwxyz_-^%@!()[];,.*$=|?';
	$yu = 0;
	do{
		//求余
		$yu = floor($int%$step);
		//求商
		$int = $int/$step;
		$result = $str[$yu].$result;
	}
	while($int>1);
	unset($int, $step, $str, $yu);
	return $result;
}

/**
 * @name 将一个字符串归类到0-9的数字中
 * @desc 归类方法是:新将字符串MD5,再取第一个字符的ASCII码数字的最后一位数字(即个位数)
 * @param string $str
 * @return integer 返回0-9中的一个数
 */
function getOneIntegerByStringASCII($str){
	$num = ord(md5($str));
	unset($str);
	return substr($num, -1, 1);
}

/**
 * @name 创建一个唯一的字符串
 * @desc
 * @return string 返回MD5后的值,32位长度
 */
function create_unique_key()
{
	return md5(microtime().uniqid().client_ip().uniqid().rand(0,99999));
}

/**
 * @name 随机获取一个字符串
 * @desc 从数字和大小写字母中随机获取一个字符串
 * @param integerduplicate argument PHPDoc $length 手机号
 * @return string
 */
function rand_string($length){
	$str = '';
	$tmp = '';
	for ($i = 1; $i <= $length; $i++) {
//		97~122是小写的英文字母
//		65~90是大写的
		$tmp = rand(87, 122);
		if($tmp<97){
			$str .= $tmp-87;
		}
		else{
			$tmp = chr($tmp);
			$str .= (rand(0,1)==1 ? strtoupper($tmp) : $tmp);
		}
	}
	unset($tmp);
	return $str;
}

/**
 * @name 判断一个字符串是不是email
 * @desc
 * @param string $email 邮箱 待检查的email字符串
 * @return boolean true表示是email格式,false表示不是email格式
 */
function is_email($email){
	return preg_match('/^[a-zA-Z0-9]+([-_.][a-zA-Z0-9]+)*@([a-zA-Z0-9]+[-.])+([a-z]{2,10})$/ims',$email);
}

/**
 * @name 检查一个密码是否安全
 * @desc 密码长度需为6-20位,且同时包含大小写字母,数字和@#$!_-中的一个符号
 * @param string $password 密码 待检测的密码字符串
 * @return boolean true表示符合最低安全要求,false表示不符合最低安全要求
 */
function is_safe_password($password){
	return preg_match('/^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*)(?=.*[\.\$!#@_-].*).{6,20}$/', $password);
}

/**
 * @name 随机生成一个密码
 * @desc 密码长度需为6-20位,且同时包含大小写字母,数字和@#$!_-中的一个符号
 * @return string
 */
function rand_a_password(){
	$password = rand(100, 99999);
	for ($i = 1; $i <= 4; $i++) {
		//97~122是小写的英文字母
		//65~90是大写的
		if(rand(1,2)===1)
		{
			$password .= chr(rand(65, 90));
		}
		else{
			$password .= chr(rand(97, 122));
		}
	}
	$str = '@#$!_-';
	$password .= $str[rand(0,5)];
	return str_shuffle($password);
}
