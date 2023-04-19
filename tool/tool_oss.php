<?php
/*
 * OSS文件上传类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 21:31
 */

use OSS\OssClient;
use OSS\Core\OssException;

class tool_oss extends base_class
{
    public function __construct()
    {
    }

    /**
     * 上传文件
     * @param string $local_file 由本地文件路径加文件名包括后缀组成，例如/users/local/myfile.txt
     * @param string $object 上传到阿里云后的文件名称，如果不传此参数，则与本地文件路径一致，本地文件路径加文件名包括后缀组成
     * @return string 保存后的文件外网访问地址
     */
    public function upload_file($local_file, $object=null)
    {
        $this->check_aliyun_config();
        if (empty($object)){
            $object = explode(APP_PATH.'static/', $local_file);
            if (empty($object[1])){
                $object = date('Y/md/H/').basename($local_file);
            }
            else{
                $object = $object[1];
            }
        }

        try{
            $ossClient = new OssClient($GLOBALS['config']['oss']['aliyun']['accessKeyId'],
                $GLOBALS['config']['oss']['aliyun']['accessKeySecret'], $GLOBALS['config']['oss']['aliyun']['endpoint']);
            $options = [
//                'Content-Disposition' => 'inline',
            ];
            $ossClient->uploadFile($GLOBALS['config']['oss']['aliyun']['bucketName'], $object, $local_file, $options);
            unlink($local_file);
            return $GLOBALS['config']['oss']['aliyun']['visit_host'].$object;
        } catch(OssException $e) {
            write_applog('ERROR', '上传文件到阿里云OSS失败：'.$e->getMessage());
            return false;
        }
    }

    /**
     * 上传文件
     * @param string $object 上传到阿里云后的文件路径加文件名包括后缀组成
     * @return boolean
     */
    public function delete_file($object)
    {
        $this->check_aliyun_config();
        if (empty($object)){
            return false;
        }

        if (strpos($object, '/')===0){
            $object = APP_PATH.'static'.$object;
            if (file_exists($object)){
                unlink($object);
                return true;
            }
            write_applog('ERROR', '删除本地文件失败，文件不存在：'.$object);
            return false;
        }
        if (strpos(strtolower($object), 'http')===0){
            $temp = explode('/',$object,4);
            if (count($temp)<4){
                write_applog('ERROR', '删除阿里云OSS上的文件失败，解析文件路径失败：'.$object);
                return false;
            }
            $object = $temp[3];
            unset($temp);
        }

        try{
            $ossClient = new OssClient($GLOBALS['config']['oss']['aliyun']['accessKeyId'],
                $GLOBALS['config']['oss']['aliyun']['accessKeySecret'], $GLOBALS['config']['oss']['aliyun']['endpoint']);
            $ossClient->deleteObject($GLOBALS['config']['oss']['aliyun']['bucketName'], $object);
            return true;
        } catch(OssException $e) {
            write_applog('ERROR', '删除阿里云OSS上的文件失败：'.$e->getMessage());
            return false;
        }
    }

    /**
     * 检查阿里云的OSS配置信息是否完整
     * @return boolean
     */
    public function check_aliyun_config()
    {
        if (empty($GLOBALS['config']['oss']['aliyun']['enable']) || empty($GLOBALS['config']['oss']['aliyun']['accessKeyId'])
            || empty($GLOBALS['config']['oss']['aliyun']['accessKeySecret']) || empty($GLOBALS['config']['oss']['aliyun']['endpoint'])
            || empty($GLOBALS['config']['oss']['aliyun']['bucketName']) || empty($GLOBALS['config']['oss']['aliyun']['visit_host'])) {
            throw new validate_exception('未配置阿里云的OSS文件上传的平台，或者配置信息不完整', CODE_NOT_CONFIG_SMS_PLAT);
        }
        return true;
    }

    /**
     * 获取图片的缩略图
     * @return string
     */
    public function aliyun_thumb_image($image_url, $width=null, $height=null, $quality = null, $format = null)
    {
        try{
            $this->check_aliyun_config();
        }
        catch (validate_exception $e){
            return $image_url;
        }
        $image_url = explode('?',$image_url);
        $params = [];
        if ($width || $height){
            $tmp = ['resize'];
            if ($width){
                $tmp[] = 'w_'.$width;
            }
            if ($height){
                $tmp[] = 'h_'.$height;
            }
            $params[] = implode(',',$tmp);
        }
        if ($quality){
            $params[] = 'quality,q_'.$quality;
        }
        if ($format){
            $params[] = 'format,'.$format;
        }
        if ($params){
            return $image_url[0].'?x-oss-process=image/'.implode('/',$params);
        }
        return $image_url[0];
    }
}
