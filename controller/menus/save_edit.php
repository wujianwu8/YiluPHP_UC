<?php
/**
 * @group 菜单
 * @name 保存修改后的菜单
 * @desc
 * @method POST
 * @uri /menus/save_edit
 * @param string id 菜单ID 必选
 * @param string position 菜单位置 必选 TOP或LEFT，自定义菜单为必选项，系统菜单不用此参数
 * @param integer parent_menu 父级菜单 必选 父级菜单的ID，自定义菜单为必选项，系统菜单不用此参数
 * @param string lang_key 菜单名称 必选 填写语言键,可实现多语言的切换,如果填写了不存在的语言键,将原样输出，自定义菜单为必选项，系统菜单不用此参数
 * @param string active_preg 选中状态匹配规则 必选 选中菜单的正则表达式规则,如:\/menus\/.*，不填写则完全等于href即选中，自定义菜单为必选项，系统菜单不用此参数
 * @param string href 链接地址 可选
 * @param string link_class 链接样式 可选 链接的附加样式
 * @param string target 跳转目标 可选 即a链接的target属性的值
 * @param integer weight 排序 可选 数字越大越靠后，默认为0
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
 *  8 不能设置父级菜单为自己的ID
 *  9 选中状态匹配规则设置错误,请填写符合正则表达式规则的字符
 *  10 选中状态匹配规则设置错误,请填写符合正则表达式规则的字符
 *  11 父级菜单不存在
 *  12 父级菜单必须是首级菜单
 *  13 设置的权限不存在
 *  14 保存失败
 *  15 nav_user_avatar为保留字段，不可使用
 *  16 菜单ID参数错误，找不到对应的菜单
 *  17 菜单ID有误
 */

if (!logic_permission::I()->check_permission('user_center:edit_menu')) {
    throw new validate_exception(YiluPHP::I()->lang('not_authorized'),100);
}

$params = input::I()->validate(
    [
        'id' => 'required|integer|return',
        'position' => 'trim|string|min:3|return',
        'parent_menu' => 'integer|return',
        'lang_key' => 'trim|string|min:2|max:50|return',
        'active_preg' => 'trim|string|min:1|max:100|return',
        'weight' => 'integer|max:9999|return',
        'permission' => 'trim|string|min:3|max:64|return',
        'icon' => 'string|trim|return',
        'href' => 'string|trim|return',
        'link_class' => 'string|trim|return',
        'target' => 'string|trim|return',
    ],
    [
        'id.*' => '菜单ID有误',
        'position.*' => '菜单位置选择有误',
        'parent_menu.*' => '父级菜单填写有误',
        'lang_key.*' => '请填写菜单名称',
        'active_preg.*' => '请填写选中状态匹配规则',
        'weight.*' => '排序号填写有误',
        'permission.*' => '访问所需权限填写有误',
    ],
    [
        'id.*' => 17,
        'position.*' => 2,
        'parent_menu.*' => 3,
        'lang_key.*' => 4,
        'active_preg.*' => 5,
        'weight.*' => 6,
        'permission.*' => 7,
    ]);
//检查操作权限

if(!$menu_info = model_menus::I()->find_table(['id'=>$params['id']])){
    return code(16,'菜单ID参数错误，找不到对应的菜单');
}
$where = ['id'=>$params['id']];
if ($menu_info['type']=='SYSTEM'){
    $data = ['weight'=>intval($params['weight'])];
    model_menus::I()->update_table($where, $data);
    unset($menu_info, $where, $data, $params);
    //删除所有菜单的缓存
    redis_y::I()->del(REDIS_KEY_ALL_MENUS);
    //返回结果
    return json(CODE_SUCCESS,YiluPHP::I()->lang('save_successfully'));
}

$data = [];
if (isset($params['weight'])){
    $data['weight'] = empty($params['weight']) ? 500 : intval($params['weight']);
}
if (isset($params['icon'])){
    $data['icon'] = $params['icon'];
}
//检查必要的参数
if (isset($params['position'])){
    if (!in_array($params['position'], ['TOP', 'LEFT'])) {
        unset($params, $data);
        return code(2, '菜单位置选择有误');
    }
    $data['position'] = $params['position'];
}
if (isset($params['parent_menu'])){
    $data['parent_menu'] = intval($params['parent_menu']);
    if ($data['parent_menu'] == $params['id']){
        return code(8, '不能设置父级菜单为自己的ID');
    }
}
if (isset($params['lang_key'])){
    if (trim($params['lang_key'])=='') {
        unset($params, $data);
        return code(4, '请填写菜单名称');
    }
    if (strtolower($params['lang_key']) == 'nav_user_avatar') {
        unset($params, $data);
        return code(15, 'nav_user_avatar为保留字段，不可使用');
    }
    $data['lang_key'] = $params['lang_key'];
}

if (isset($params['active_preg'])){
    if (empty($params['active_preg'])) {
        unset($params, $data);
        return code(5, '请填写选中状态匹配规则');
    }

    //检查匹配规则的有效性
    try {
        if(@preg_match('/' . $params['active_preg'] . '/', 'test', $match) === false){
            unset($params, $data);
            return code(9, '选中状态匹配规则设置错误,请填写符合正则表达式规则的字符');
        }
    } catch (Exception $e) {
        unset($params, $data);
        return code(10, '选中状态匹配规则设置错误,请填写符合正则表达式规则的字符');
    }
    $data['active_preg'] = $params['active_preg'];
}

//检查父级菜单的有效性
if(isset($params['parent_menu'])) {
    if (!empty($params['parent_menu']) && !$parent = model_menus::I()->find_table(['id' => $params['parent_menu']])) {
        unset($params, $parent, $data);
        return code(11, '父级菜单不存在');
    }

    if (!empty($params['parent_menu']) && !empty($parent['parent_menu'])) {
        unset($params, $parent, $data);
        return code(12, '父级菜单必须是首级菜单');
    }
    $data['parent_menu'] = intval($params['parent_menu']);
}
//检查权限是否存在
if (isset($params['permission'])) {
    if (!empty($params['permission'])) {
        $temp = explode(':', $params['permission']);
        if (count($temp)!=2 || !$permission = model_permission::I()->find_table(['app_id' => $temp[0], 'permission_key' => $temp[1]], 'permission_id')) {
            unset($permission, $params, $parent, $data, $temp);
            return code(13, '设置的权限不存在');
        }
        unset($permission, $temp);
        $data['permission'] = $params['permission'];
    }
    else{
        $data['permission'] = '';
    }
}
//检查链接样式是否存在
if (isset($params['link_class'])) {
    $data['link_class'] = trim($params['link_class']);
}
if (isset($params['href'])) {
    $data['href'] = $params['href'];
}

if(count($data)==0){
    return json(CODE_SUCCESS,YiluPHP::I()->lang('save_successfully'));
}

//保存入库
if(!model_menus::I()->update_table($where, $data)){
    unset($params, $parent, $data);
    return code(14, '保存失败');
}

//删除所有菜单的缓存
redis_y::I()->del(REDIS_KEY_ALL_MENUS);

unset($params, $parent, $menu_info, $where, $data);
//返回结果
return json(CODE_SUCCESS,YiluPHP::I()->lang('save_successfully'));
