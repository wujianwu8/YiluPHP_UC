<?php
/**
 * @group 用户
 * @name 保存添加的新用户
 * @desc
 * @method POST
 * @uri /user/save_add
 * @param string nickname 昵称 必选 昵称
 * @param integer area_code 手机归属地编码 必选 手机归属地编码
 * @param integer mobile 登录手机 必选 登录手机
 * @param string username 登录名 可选 登录名由字母、数字、下划线、中横杆、点组成，且至少包含一个非数字，长度为3-50个字
 * @param string password 登录密码 必选 登录密码，密码长度需为6-20个字符，且同时包含大小写字母、数字和@#$!_-中的一个符号
 * @param string gender 性别 必选 性别
 * @param string birthday 生日 必选 格式如：2019-06-08
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 保存成功
 *  1 保存失败
 *  2 登录名错误：登录名由字母、数字、下划线、中横杆、点组成，且至少包含一个非数字，长度为3-50个字
 *  3 已有登录名，不可修改
 *  4 必须设置一个昵称
 *  5 性别设置错误
 *  6 国家设置错误
 *  7 生日设置错误
 */

$params = input::I()->validate(
    [
        'nickname' => 'required|required|string|min:1|return',
        'area_code' => 'trim|integer|min:1|return',
        'mobile' => 'required|trim|integer|min:10000|return',
        'password' => 'required|trim|string|min:6|max:20|return',
        'gender' => 'required|trim|string|min:4|return',
        'birthday' => 'required|trim|string|min:10|return',
    ]);
//检查操作权限
if (!logic_permission::I()->check_permission('user_center:add_user')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

//检查此手机有没有注册过
$complete_phone = $params['area_code'].'-'.$params['mobile'];
if($uid = model_user_identity::I()->find_uid_by_identity('INNER', $complete_phone)){
    unset($params);
    return code(4, YiluPHP::I()->lang('mobile_is_signed_up'));
}

if(!is_safe_password($params['password'])){
    unset($params);
    return code(7, YiluPHP::I()->lang('password_too_simple'));
}

if(!$uid = logic_uuid::I()->get_uuid('uid',1)){
    unset($params, $complete_phone);
    return code(1, YiluPHP::I()->lang('failed_to_create_uid'));
}

$time = time();
$user_info = [
    'uid' => $uid,
    'password' => $params['password'],
    'salt' => uniqid(),
    'mtime' => $time,
    'ctime' => $time,
    'type' => 'INNER',
    'identity' => $complete_phone,
];

//保存入库
if(!$uid = logic_user::I()->create_user($user_info)){
    unset($params, $complete_phone, $user_info);
    return code(1, YiluPHP::I()->lang('	save_failed'));
}

//保存其它信息
$where = ['uid'=>$uid];
$data = [
    'nickname' => $params['nickname'],
    'birthday' => $params['birthday'],
    'gender' => $params['gender'],
];
model_user::I()->update_table($where, $data);

//返回结果
return json(CODE_SUCCESS,YiluPHP::I()->lang('save_successfully'));
