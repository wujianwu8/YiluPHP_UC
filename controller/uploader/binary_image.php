<?php
/**
 * @group 用户
 * @name 保存用户头像
 * @desc
 * @method POST
 * @uri /setting/save_avatar
 * @param string avatar 头像 必选 图片文件的Base64字符串
 * @param string type 文件用途类型 可选 对应file表的type字段，由各子系统自定义，如avatar，默认空字符串，为空意味着以后这个文件很难追踪
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

$params = input::I()->validate(
    [
        'avatar' => 'required|string|min:50|return',
        'type' => 'string|max:32|return',
    ],
    [
        'avatar' => '请选择照片并裁剪后再保存',
        'type' => '文件类型type过长',
    ],
    [
        'avatar' => 2,
        'type' => 3,
    ]);
//检查操作权限

$img = str_replace('data:image/png;base64,', '', $params['avatar']);
$img = str_replace(' ', '+', $img);
$data = base64_decode($img);

$path = '/avatar/'.date('Y').'/'.date('md').'/'.date('H').'/';
if (!is_dir(APP_PATH.'static'.$path)) {
    mkdir(APP_PATH.'static'.$path, 0777, true);
}
$file_name = '300x300WxH'.md5(uniqid().microtime().uniqid()).'.png';
$fp = fopen(APP_PATH.'static'.$path.$file_name, 'w');
fwrite($fp, $data);
fclose($fp);

$avatar = $local_url = $path.$file_name;
if (!empty($GLOBALS['config']['oss']['aliyun']['enable'])) {
    $avatar = tool_oss::I()->upload_file(APP_PATH . 'static/' . substr($avatar, 1));
}
$data = [
    'avatar'=>$avatar,
];
$where = ['uid'=>$self_info['uid']];
//保存入库
if(!logic_user::I()->update_user_info($where, $data)){
    if (!empty($GLOBALS['config']['oss']['aliyun']['enable'])) {
        $avatar = tool_oss::I()->delete_file($avatar);
    }
    unset($params, $where, $data, $path, $file_name, $img, $fp, $avatar);
    return code(1, '保存失败');
}
if (!empty($GLOBALS['config']['oss']['aliyun']['enable'])) {
    $avatar = tool_oss::I()->delete_file($self_info['avatar']);
}

//文件上传记录保存入库
$type = (string)input::I()->request('type', '');
$uid = empty($self_info['uid']) ? 0 : intval($self_info['uid']);
model_file::I()->insert_file($local_url, $type, $uid, client_ip());

//更新当前登录者的session信息
logic_user::I()->update_current_user_info($data);
unset($params, $where, $data, $path, $file_name, $img, $fp, $avatar);
//返回结果
return json(0,YiluPHP::I()->lang('save_successfully'));