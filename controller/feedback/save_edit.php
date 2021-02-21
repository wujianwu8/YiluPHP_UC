<?php
/**
 * @group 用户反馈
 * @name 保存修改后的反馈
 * @desc
 * @method POST
 * @uri /feedback/save_edit
 * @param integer id 反馈ID 必选
 * @param integer status 反馈状态 可选 反馈状态：0新反馈、1正在处理、2已处理
 * @param string remark 备注 可选 管理员备注信息
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 保存成功
 *  1 保存失败
 *  2 反馈ID参数错误
 *  3 反馈信息不存在
 */

if (!logic_permission::I()->check_permission('user_center:deal_with_feedback')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

$params = input::I()->validate(
    [
        'id' => 'required|integer|return',
        'status' => 'trim|string|return',
        'remark' => 'trim|string|return',
    ],
    [
        'id.*' => '反馈ID参数错误',
    ],
    [
        'id.*' => 2,
    ]);
//检查操作权限

if(!$check_info = model_user_feedback::I()->find_table(['id'=>$params['id']])){
    throw new validate_exception('反馈信息不存在',3);
}
unset($check_info);
$where = ['id'=>$params['id']];
$data = [];

if (isset($params['status']) && $params['status']!==null){
    $data['status'] = intval($params['status']);
}
if (isset($params['remark'])){
    $data['remark'] = $params['remark'];
}
if(count($data)==0){
    return json(CODE_SUCCESS,'保存成功');
}

//保存入库
if(!model_user_feedback::I()->update_table($where, $data)){
    unset($params, $where, $data);
    throw new validate_exception('保存失败',1);
}

unset($params, $where, $data);
//返回结果
return json(CODE_SUCCESS,'保存成功');
