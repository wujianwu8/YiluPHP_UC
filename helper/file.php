<?php
/*
 * 文件处理类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 19:22
 */

class file extends base_class
{
	public function __construct()
	{
	}

	public function __destruct()
	{
	}

    /**
     * @name 下载一张远程图片到本地
     * @desc
     * @param string $url 图片地址
     * @param string $path 保存到本地的目录（在项目的static目录下创建）
     * @return string 返回本地的图片访问地址
     */
    public function download_image($url, $path = 'images/')
    {
        if (empty($url)){
            return '';
        }
        $extension = $this->check_file_type($url);
        if(!in_array($extension, ['jpg', 'png', 'bpm', 'gif'])){
            return '';
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        if (substr($path,0,1)=='/'){
            $path = substr($path, 1);
        }
        if (strlen($path)>0 && substr($path,-1)!=='/'){
            $path .= '/';
        }
        $local_path = APP_PATH . 'static/'.$path;
        !is_dir($local_path) && mkdir($local_path, 0777, true);
        $filename = md5(uniqid().time().uniqid()).'.'.$extension;
        $resource = fopen($local_path . $filename, 'a');
        fwrite($resource, $file);
        fclose($resource);
        $img_info = getimagesize($local_path . $filename);
        $rename = $img_info[0].'x'.$img_info[1].'WxH'.$filename;
        rename($local_path . $filename, $local_path . $rename);
        return '/'.$path.$rename;
    }


    function check_file_type($file_name){
        $file = fopen($file_name, "rb");
        $bin = fread($file, 2); //只读2字节
        fclose($file);
        // C为无符号整数，网上搜到的都是c，为有符号整数，这样会产生负数判断不正常
        $str_info = @unpack("C2chars", $bin);
        $type_code = intval($str_info['chars1'].$str_info['chars2']);
        switch( $type_code )
        {
            case '255216':
                return 'jpg';
                break;
            case '7173':
                return 'gif';
                break;
            case '13780':
                return 'png';
                break;
            case '6677':
                return 'bmp';
                break;
            case '7790':
                return 'exe';
                break;
            case '7784':
                return 'midi';
                break;
            case '8297':
                return 'rar';
                break;
            default:
                return 'Unknown';
                break;
        }
    }
}
