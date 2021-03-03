<?php
/*
 * 权限相关的逻辑处理类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 17:10
 */


class logic_permission extends base_class
{
	public function __construct()
	{
	}

	public function __destruct()
	{
	}

    /**
     * @name 检查当前登录用户是否具有某权限
     * @desc
     * @param string $format_permission_key 格式化后的权限键名，格式如：app_id:permission_key
     * @return boolean
     * @throws
     */
    public function check_permission($format_permission_key)
    {
        global $self_info;
        if (empty($self_info['uid'])){
            return false;
        }
        $tmp = explode(':', $format_permission_key);
        if (count($tmp)!=2){
            return false;
        }
        return model_user_permission::I()->if_has_permission($self_info['uid'], $tmp[1], $tmp[0]);
    }

    /**
     * @name 通过角色ID删除用户已有权限的缓存
     * @desc
     * @param integer $role_id 角色ID
     * @param string $app_id 应用ID
     * @return boolean
     * @throws
     */
    public function delete_user_permission_cache_by_role_id($role_id, $app_id='')
    {
        if (!$app_id) {
            //读取此角色包含的所有系统
            if (!$app_ids = model_role_permission::I()->select_all_app_id_of_role($role_id)) {
                unset($app, $app_ids);
                return true;
            }
        }
        else{
            $app_ids = [$app_id];
        }

        //读取拥有此角色的所有人
        if(!$uids = model_user_role::I()->select_all(['role_id'=>$role_id], '', 'uid')){
            unset($app, $uids);
            return true;
        }

        foreach ($uids as $item){
            redis_y::I()->del(REDIS_KEY_USER_PERMISSION.$item['uid']);
            foreach($app_ids as $app_id){
                redis_y::I()->del(REDIS_KEY_USER_PERMISSION.$item['uid'].'_'.$app_id);
            }
        }
        unset($app, $uids, $item, $app_ids);
        return true;
    }

    /**
     * @name 通过权限ID删除用户已有权限的缓存
     * @desc
     * @param integer $permission_id 角色ID
     * @param string $app_id 应用ID
     * @return boolean
     * @throws
     */
    public function delete_user_permission_cache_by_permission_id($permission_id, $app_id='')
    {
        if (!$app_id){
            if ($app_id = model_permission::I()->find_table(['permission_id'=>$permission_id], 'app_id')){
                $app_id = $app_id['app_id'];
            }
        }
        //读取拥有此权限的所有人
        if(!$uids = model_user_permission::I()->select_all(['permission_id'=>$permission_id], '', 'uid')){
            unset($app, $uids);
            return true;
        }
        foreach ($uids as $item){
            redis_y::I()->del(REDIS_KEY_USER_PERMISSION.$item['uid']);
            if ($app_id){
                redis_y::I()->del(REDIS_KEY_USER_PERMISSION.$item['uid'].'_'.$app_id);
            }
        }
        unset($app, $uids, $item);
        return true;
    }

    /**
     * @name 获取指定用户可以分配的权限
     * @desc
     * @param integer $uid 用户ID
     * @param string $app_id 应用ID
     * @return array
     * @throws
     */
    public function select_user_permission_can_grant_in_app($uid, $app_id)
    {
        if ($user_permission_list = model_user_permission::I()->select_permissions_user_already_has($uid, $app_id)){
            $permission_ids = [];
            foreach ($user_permission_list as $item){
                $item = explode(':', $item);
                if (strpos($item[1],'grant_')===0){
                    $permission_ids[] = substr($item[1],6);
                }
            }
            if ($permission_ids) {
                $where = [
                    'app_id' => $app_id,
                    'permission_key' => ['symbol' => 'IN', 'value' => $permission_ids]
                ];
                $app_permission_list = model_permission::I()->select_all($where, '', 'permission_id,permission_key,permission_name,description');
            }
            else{
                $app_permission_list = [];
            }
        }
        else{
            $app_permission_list = [];
        }
        return $app_permission_list;
    }

    /**
     * @name 获取指定用户可以分配的权限
     * @desc
     * @param integer $uid 用户ID
     * @param string $app_id 应用ID
     * @return array
     * @throws
     */
    public function select_user_permission_ids_in_app($uid, $app_id)
    {
        if ($user_permission_list = model_user_permission::I()->select_permissions_user_already_has($uid, $app_id)){
            $permission_ids = [];
            foreach ($user_permission_list as $item){
                $item = explode(':', $item);
                $permission_ids[] = $item[1];
            }
            if ($permission_ids) {
                $where = [
                    'app_id' => $app_id,
                    'permission_key' => ['symbol' => 'IN', 'value' => $permission_ids]
                ];
                $app_permission_list = model_permission::I()->select_all($where, '', 'permission_id');
            }
            else{
                $app_permission_list = [];
            }
        }
        else{
            $app_permission_list = [];
        }
        return $app_permission_list;
    }
}
