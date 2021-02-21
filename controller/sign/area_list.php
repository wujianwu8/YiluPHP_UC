<?php
/**
 * @group 用户
 * @name 国家地区码
 * @desc 例如用于登录时选择手机的地区码
 * @method GET
 * @uri /sign/area_list
 * @return json
 */

$area_list = lib_ip::I()->getAutoAreaList();
//foreach ($area_list as $key => $value){
//    $area_list[$key]['name'] = '（+'.$value['code_number'].'）'.$value['name'];
//}
return json(0,'获取成功', $area_list);