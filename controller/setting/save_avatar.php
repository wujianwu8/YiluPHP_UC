<?php
/**
 * @group 用户
 * @name 保存用户头像
 * @desc
 * @method POST
 * @uri /setting/save_avatar
 * @param string avatar 头像 必选 图片文件的Base64字符串
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 保存成功
 *  1 保存失败
 *  2 请选择照片并裁剪后再保存
 *  3
 *  4 密码错误
 *  5 邮箱错误
 *  6 验证码错误或已失效
 *  7 验证码错误
 *  8 密码错误
 */

$params = $app->input->validate(
    [
        'avatar' => 'required|string|min:50|return',
    ],
    [
        'avatar' => '请选择照片并裁剪后再保存',
    ],
    [
        'avatar' => 2,
    ]);
//检查操作权限

$img = str_replace('data:image/png;base64,', '', $params['avatar']);
$img = str_replace(' ', '+', $img);
$data = base64_decode($img);

$path = '/avatar/'.date('Y').'/'.date('md').'/'.date('H').'/';
if (!is_dir($project_root.'static'.$path)) {
    mkdir($project_root.'static'.$path, 0777, true);
}
$file_name = '300x300WxH'.md5(uniqid().microtime().uniqid()).'.png';
$fp = fopen($project_root.'static'.$path.$file_name, 'w');
fwrite($fp, $data);
fclose($fp);

$avatar = $path.$file_name;
if (!empty($GLOBALS['config']['oss']['aliyun'])) {
    $avatar = $app->tool_oss->upload_file($project_root . 'static/' . substr($avatar, 1));
}
$data = [
    'avatar'=>$avatar,
];
$where = ['uid'=>$self_info['uid']];
//保存入库
if(!$app->logic_user->update_user_info($where, $data)){
    if (!empty($GLOBALS['config']['oss']['aliyun'])) {
        $avatar = $app->tool_oss->delete_file($avatar);
    }
    unset($params, $where, $data, $path, $file_name, $img, $fp, $avatar);
    return_code(1, '保存失败');
}
if (!empty($GLOBALS['config']['oss']['aliyun'])) {
    $avatar = $app->tool_oss->delete_file($self_info['avatar']);
}
//更新当前登录者的session信息
$app->logic_user->update_current_user_info($data);
unset($params, $where, $data, $path, $file_name, $img, $fp, $avatar);
//返回结果
return_json(CODE_SUCCESS,'保存成功');