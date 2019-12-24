<?php
/**
 * @name 使用表单上传图片
 * @desc $_FILES读取文件
 * @method POST
 * @uri /uploader/form_image
 * @param binary file 文件参数名 必选 图片文件的参数名
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 上传成功
 *  1 缺少参数名为image的文件
 *  2 检查上传文件有效性时的相关错误
 *  3 文件上传时的相关错误
 */

if (!isset($_FILES['image'])){
    return_code(1, '缺少参数名为image的文件');
}

if (tool_file_uploader::check_one($_FILES['image'])){
    return_code(2, tool_file_uploader::$error);
}
$path = '/upload/image/'.date('Y').'/'.date('md').'/'.date('H').'/';
if (!$file_name = tool_file_uploader::upload_one($_FILES['image'], $project_root.'static'.$path)){
    return_code(3, tool_file_uploader::$error);
}

$file_url = $path.$file_name;
if (!empty($GLOBALS['config']['oss']['aliyun'])) {
    $file_url = $app->tool_oss->upload_file($project_root . 'static/' . substr($file_url, 1));
}
$with = $app->input->request_int('with', 1000);
$quality = $app->input->request_int('quality', 80);
$data = [
    'original_url'=>$file_url,
    'file_url'=>$app->tool_oss->aliyun_thumb_image($file_url, $with, null, $quality, 'webp'),
];

//文件上传记录保存入库

//返回结果
return_json(0,'上传成功', $data);