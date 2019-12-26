<?php
/**
 * @group 用户
 * @name 保存账号相关信息
 * @desc
 * @method POST
 * @uri /setting/save_info
 * @param string nickname 昵称 可选 如果传了此参数，则不能为空且必须含有字符
 * @param string gender 性别 可选 如果传了此参数，则不能为空
 * @param string birthday 生日 可选 格式如：2019-06-08
 * @param string country 国家 可选 国家的语言键名
 * @param string province 省份 可选 省份
 * @param string city 城市 可选 城市
 * @param string username 登录名 可选 登录名由字母、数字、下划线、中横杆、点组成，且至少包含一个非数字，长度为3-50个字
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

$params = $app->input->validate(
    [
        'nickname' => 'string|min:1|return',
        'gender' => 'trim|string|min:4|return',
        'birthday' => 'trim|string|min:10|return',
        'country' => 'trim|string|min:10|return',
        'province' => 'trim|string|return',
        'city' => 'trim|string|return',
        'username' => 'trim|string|return',
    ],
    [
        'nickname' => '必须设置一个昵称',
        'gender' => '性别设置错误',
        'birthday' => '生日设置错误',
        'country' => '国家设置错误',
    ],
    [
        'nickname' => 4,
        'gender' => 5,
        'birthday' => 7,
        'country' => 6,
    ]);
//检查操作权限

if (isset($params['username']) && $params['username'] != ''){
    $params['username'] = strtolower($params['username']);
    if(!preg_match('/^[\w\d_\-\.]{3,50}$/', $params['username'], $match) || preg_match('/^\d+$/', $params['username'], $match)){
        unset($params);
        return_code(2, '登录名错误：登录名由字母、数字、下划线、中横杆、点组成，且至少包含一个非数字，长度为3-50个字');
    }
    //检查当前用户是否已经设置登录名
    $identity = $app->model_user_identity->select_all(['uid'=>$self_info['uid']], '', 'type,identity', $self_info['uid']);
    foreach ($identity as $item){
        if ($item['type']=='INNER'){
            if($app->logic_user->get_identity_type($item['identity']) == 'username' ){
                unset($params, $identity, $item);
                return_code(3, '已有登录名，不可修改');
            }
        }
    }
    //设置用户名
    if (!$app->model_user_identity->insert_identity(['uid' =>$self_info['uid'], 'type'=>'INNER', 'identity'=>$params['username'] ])){
        unset($params, $identity, $item);
        return_code(1, '保存失败');
    }
    unset($identity, $item);
}

$data = [];
if (isset($params['nickname'])){
    if (trim($params['nickname'])=='') {
        unset($params, $data);
        return_code(4, '必须设置一个昵称');
    }
    $data['nickname'] = $params['nickname'];
}
if (isset($params['gender'])){
    if (!in_array($params['gender'], ['male', 'female'])) {
        unset($params, $data);
        return_code(5, '性别设置错误');
    }
    $data['gender'] = $params['gender'];
}

if (isset($params['birthday'])){
    $data['birthday'] = $params['birthday']?$params['birthday']:null;
}
if (isset($params['country'])){
    $country_lang_keys = $app->lib_address->selectCountryLangKeys();
    if (array_search($params['country'], $country_lang_keys)==false){
        unset($params, $data, $country_lang_keys);
        return_code(6, '国家设置错误');
    }
    $data['country'] = $params['country'];
}
if (isset($params['province'])){
    $data['province'] = $params['province'];
}
if (isset($params['city'])){
    $data['city'] = $params['city'];
}

if(count($data)==0){
    return_json(CODE_SUCCESS,'保存成功');
}
$where = ['uid'=>$self_info['uid']];
//保存入库
if(!$app->logic_user->update_user_info($where, $data)){
    unset($params, $where, $data);
    return_code(1, '保存失败');
}
//更新当前登录者的session信息
$app->logic_user->update_current_user_info($data);
unset($params, $where, $data);
//返回结果
return_json(CODE_SUCCESS,'保存成功');
