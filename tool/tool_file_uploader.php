<?php
/*
 * O文件上传类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 10:31
 */

class tool_file_uploader extends base_class
{
    //设定属性：保存允许上传的MIME类型
    private static $types = array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png');
    //判断为图片的文件类型，图片文件名不一样的方式，会包含宽和高信息
    private static $image_types = array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png');
    //修改允许上传类型
    public static function setTypes($types = array()){
        //判定是否为空
        if (!empty($types)) self::$types = $types;
    }
    public static $error;    //记录单文件上传过程中出现的错误信息
    public static $errors;   //记录多文件上传过程中出现的错误信息
    public static $files;    //记录多文件上传成功后文件名对应信息

    /**
     * @desc 检查单文件的合法性
     * @param string $file，上传文件信息数组
     * @param int $max，默认10M，最大上传大小，单位字节
     * @return bool|string，成功返回文件名，失败返回false
     */
    public static function check_one($file, $max = 10485760){
        //判定文件有效性
        if (!isset($file['error']) || count($file) != 5) {
            self::$error = '错误的上传文件！';
            return false;
        }
        //判定文件是否正确上传
        switch ($file['error']) {
            case 1:
            case 2:
                self::$error = '文件超过服务器允许大小！';
                return false;
            case 3:
                self::$error = '文件只有部分被上传！';
                return false;
            case 4:
                self::$error = '没有选中要上传的文件！';
                return false;
            case 6:
            case 7:
                self::$error = '服务器错误！';
                return false;
        }
        //判定文件类型
        if (!in_array($file['type'], self::$types)) {
            self::$error = '当前上传的文件类型不允许！';
            return false;
        }
        //判定文件大小
        if ($file['size'] > $max) {
            self::$error = '当前上传的文件超过允许的大小！当前允许的大小是：' . self::format_file_size($max);
            return false;
        }
    }

    /**
     * @desc 格式化文件大小
     * @param int $byte，文件大小，单位字节
     * @return string，返回文件阅读的大小描述
     */
    public static function format_file_size($byte){
        $byte = $byte/1024;
        if ($byte<1024){
            return round($byte, 2).'KB';
        }
        return round($byte/1024, 2).'M';
    }

    /**
     * @desc 检查多个文件的合法性
     * @param array $files，多个上传文件信息二维数组
     * @param int $max，默认10M，最大上传大小，单位字节
     * @return bool|string，成功返回文件名，失败返回false
     */
    public static function check_all($files, $max = 10485760){
        for($i = 0, $len = count($files['name']); $i < $len; $i++){
            $file = array(
                'name'        =>$files['name'][$i],
                'type'        =>$files['type'][$i],
                'tmp_name'    =>$files['tmp_name'][$i],
                'error'        =>$files['error'][$i],
                'size'        =>$files['size'][$i]
            );
            $res = self::check_all($file, $max);
            if(!$res){
                //错误处理
                $error = self::$error;
                self::$errors[] = "文件：{$file['name']}上传失败:{$error}!<br>";
            }else{
                self::$files[] = $file['name']. '=>'. $res;
            }
        }
        if(!empty(self::$errors)){
            return false;
        }else{
            return true;
        }
    }

    /**
     * @desc 单文件上传
     * @param string $file,上传文件信息数组
     * @param string $path,上传路径
     * @param string $filename，指定文件名，如果不指定则会随机产生一个
     * @return bool|string,成功返回文件名，失败返回false
     */
    public static function upload_one($file, $path, $filename=''){
        //路径判定
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                self::$error = '创建存储路径失败：'.$path;
                return false;
            }
        }
        if (!$filename) {
            //获取随机名字
            if (in_array($file['type'], self::$image_types)) {
                $filename = self::getRandomImageName($file);
            }
            else{
                $filename = self::getRandomName($file['name']);
            }
        }
        //移动上传的临时文件到指定目录
        if (move_uploaded_file($file['tmp_name'], $path . '/' . $filename)) {
            //成功
            return $filename;
        } else {
            //失败
            self::$error = '文件移动失败！';
            return false;
        }
    }

    /**
     * @desc 多文件上传
     * @param array $files,上传文件信息二维数组
     * @param string $path,上传路径
     * @return bool 是否全部上传成功
     */
    public static function upload_all($files, $path){
        for($i = 0, $len = count($files['name']); $i < $len; $i++){
            $file = array(
                'name'        =>$files['name'][$i],
                'type'        =>$files['type'][$i],
                'tmp_name'    =>$files['tmp_name'][$i],
                'error'        =>$files['error'][$i],
                'size'        =>$files['size'][$i]
            );
            $res = self::upload_one($file, $path);
            if(!$res){
                //错误处理
                $error = self::$error;
                self::$errors[] = "文件：{$file['name']}上传失败:{$error}!<br>";
            }else{
                self::$files[] = $file['name']. '=>'. $res;
            }
        }
        if(!empty(self::$errors)){
            //错误处理
            //var_dump(self::$errors);
            return false;
        }else{
            return true;
        }
    }

    /**
     * @desc 获取随机文件名
     * @param string $filename,文件原名
     * @return string,返回新文件名
     */
    public static function getRandomName($filename){
        //取出源文件后缀
        $ext = strrchr($filename, '.');
        //构建新名字
        $new_name = md5(uniqid().microtime().uniqid());
        //返回最终结果
        return $new_name . $ext;
    }

    /**
     * @desc 获取随机文件名
     * @param string $file，上传文件信息数组
     * @return string，返回新文件名
     */
    public static function getRandomImageName($file){
        //取出源文件后缀
        $ext = strrchr($file['name'], '.');
        //获取图片尺寸
        $temp = getimagesize($file['tmp_name']);
        //构建新名字
        $new_name = $temp[0].'x'.$temp[1].'WxH'.md5(uniqid().microtime().uniqid());
        //返回最终结果
        return $new_name . $ext;
    }
}
