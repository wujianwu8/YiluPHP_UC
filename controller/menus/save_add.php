<?php
/**
 * @group 菜单
 * @name 保存新菜单
 * @desc
 * @method POST
 * @uri /menus/save_add
 * @param string position 菜单位置 必选 TOP或LEFT
 * @param integer parent_menu 父级菜单 必选 父级菜单的ID
 * @param string lang_key 菜单名称 必选 填写语言键,可实现多语言的切换,如果填写了不存在的语言键,将原样输出
 * @param string active_preg 选中状态匹配规则 必选 选中菜单的正则表达式规则,如:\/menus\/.*，不填写则完全等于href即选中
 * @param string href 链接地址 可选
 * @param string link_class 链接样式 可选 链接的附加样式
 * @param string target 跳转目标 可选 即a链接的target属性的值
 * @param integer weight 排序 可选 数字越大越靠后，默认为500
 * @param string icon 图标样式或HTML代码 可选 5位以内的纯数字
 * @param string permission 访问所需权限 可选 格式化后的权限键名，格式如：app_id:permission_key
 * @return json
 * {
 *      code: 0
 *      ,data: []
 *      ,msg: "保存成功"
 * }
 * @exception
 *  0 保存成功
 *  1 菜单位置选择有误
 *  2 手机号码填写有误
 *  3 父级菜单填写有误
 *  4 请填写菜单名称
 *  5 请填写选中状态匹配规则
 *  6 排序号填写有误
 *  7 访问所需权限填写有误
 *  8 菜单位置参数错误
 *  9 选中状态匹配规则设置错误,请填写符合正则表达式规则的字符
 *  10 选中状态匹配规则设置错误,请填写符合正则表达式规则的字符
 *  11 父级菜单不存在
 *  12 父级菜单必须是首级菜单
 *  13 设置的权限不存在
 *  14 保存失败
 *  15 nav_user_avatar为保留字段，不可使用
 */

if (!$app->logic_permission->check_permission('user_center:add_menu')) {
    return_code(100, $app->lang('not_authorized'));
}

$params = $app->input->validate(
    [
        'position' => 'required|trim|string|min:3|return',
        'parent_menu' => 'required|integer|return',
        'lang_key' => 'required|trim|string|min:2|max:50|return',
        'active_preg' => 'required|trim|string|min:1|max:100|return',
        'weight' => 'integer|max:9999|return',
        'permission' => 'trim|string|min:3|max:64|return',
        'icon' => 'string|trim|return',
        'href' => 'string|trim|return',
        'link_class' => 'string|trim|return',
        'target' => 'string|trim|return',
    ],
    [
        'position.*' => '菜单位置选择有误',
        'parent_menu.*' => '父级菜单填写有误',
        'lang_key.*' => '请填写菜单名称',
        'active_preg.*' => '请填写选中状态匹配规则',
        'weight.*' => '排序号填写有误',
        'permission.*' => '访问所需权限填写有误',
    ],
    [
        'position.*' => 2,
        'parent_menu.*' => 3,
        'lang_key.*' => 4,
        'active_preg.*' => 5,
        'weight.*' => 6,
        'permission.*' => 7,
    ]);
//检查操作权限
//检查位置的有效性
if(!in_array($params['position'], ['TOP','LEFT'])){
    unset($params);
    return_code(8,'菜单位置参数错误');
}
if (strtolower($params['lang_key']) == 'nav_user_avatar'){
    unset($params);
    return_code(15,'nav_user_avatar为保留字段，不可使用');
}
//检查匹配规则的有效性
if($params['active_preg']) {
    try {
        if(@preg_match('/' . $params['active_preg'] . '/', 'test', $match) === false){
            return_code(9, '选中状态匹配规则设置错误,请填写符合正则表达式规则的字符');
        }
    } catch (Exception $e) {
        return_code(10, '选中状态匹配规则设置错误,请填写符合正则表达式规则的字符');
    }
}
else{
    $params['active_preg'] = '';
}
//检查父级菜单的有效性
if(!empty($params['parent_menu']) && !$parent = $app->model_menus->find_table(['id'=>$params['parent_menu']])){
    unset($params, $parent);
    return_code(11,'父级菜单不存在');
}
if(!empty($parent['parent_menu'])){
    unset($params, $parent);
    return_code(12,'父级菜单必须是首级菜单');
}
//检查权限是否存在
if (isset($params['permission'])) {
    if (!empty($params['permission'])) {
        $temp = explode(':', $params['permission']);
        if (count($temp)!=2 || !$permission = $app->model_permission->find_table(['app_id' => $temp[0], 'permission_key' => $temp[1]], 'permission_id')) {
            unset($permission, $params, $parent, $data, $temp);
            return_code(13, '设置的权限不存在');
        }
        unset($permission, $temp);
        $data['permission'] = $params['permission'];
    }
    else{
        $data['permission'] = '';
    }
}

//保存入库
$params['type'] = 'CUSTOMIZE';
$params['ctime'] = time();
if(!$app->model_menus->insert_table($params)){
    unset($params, $parent);
    return_code(14, '保存失败');
}

//删除所有菜单的缓存
$app->redis()->del(REDIS_KEY_ALL_MENUS);

unset($params, $parent);
//返回结果
return_json(CODE_SUCCESS,'保存成功');
