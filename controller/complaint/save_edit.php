<?php
/**
 * @group 用户投诉
 * @name 保存修改后的投诉
 * @desc
 * @method POST
 * @uri /complaint/save_edit
 * @param integer id 投诉ID 必选
 * @param integer status 投诉状态 可选 投诉状态：0新投诉、1正在处理、2已处理
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
 *  2 投诉ID参数错误
 *  3 投诉不存在
 */

if (!logic_permission::I()->check_permission('user_center:deal_with_complaint')) {
    return code(100, YiluPHP::I()->lang('not_authorized'));
}

$params = input::I()->validate(
    [
        'id' => 'required|integer|return',
        'status' => 'trim|string|return',
        'remark' => 'trim|string|return',
    ],
    [
        'id.*' => '投诉ID参数错误',
    ],
    [
        'id.*' => 2,
    ]);
//检查操作权限

if(!$check_info = model_user_complaint::I()->find_table(['id'=>$params['id']])){
    return code(3,'投诉不存在');
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
if(!model_user_complaint::I()->update_table($where, $data)){
    unset($params, $where, $data);
    return code(1, '保存失败');
}

unset($params, $where, $data);
//返回结果
return json(CODE_SUCCESS,'保存成功');
