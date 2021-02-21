<?php
/*
 * 应用逻辑处理类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 21:39
 */

class logic_application extends base_class
{
	public function __construct()
	{
	}

	public function __destruct()
	{
	}

    /**
     * @name 删除应用
     * @desc
     * @param string $app_id 应用ID
     * @return boolean
     * @throws
     */
    public function delete_application($app_id)
    {
        //查找出应用的所有权限
        if($permission_ids = model_permission::I()->select_all(['app_id'=>$app_id], 'permission_id')) {
            $permission_ids = array_column($permission_ids, 'permission_id');
            //删除（包含该应用的权限的）用户权限
            model_user_permission::I()->destroy(['permission_id' => [
                'symbol' => 'IN',
                'value' => $permission_ids,
            ]]);
            //删除（包含该应用的权限的）角色权限
            model_role_permission::I()->destroy(['permission_id' => [
                'symbol' => 'IN',
                'value' => $permission_ids,
            ]]);
            //删除应用的权限
            model_permission::I()->destroy(['app_id' => $app_id]);
        }
        //删除应用信息
        model_application::I()->delete(['app_id'=>$app_id]);
        unset($app, $app_id, $permission_ids);
        return true;
    }

    /**
     * @name 翻译权限名
     * @desc
     * @param string $permission_name 权限名
     * @param string $permission_key 权限键名
     * @return boolean
     * @throws
     */
    public function translate_permission_name($permission_name, $permission_key){
        $permission_name = stripslashes($permission_name);
        if (strpos($permission_key, 'grant_grant_')===0){
            return YiluPHP::I()->lang('grant_for_grant_permission_name', ['name'=>YiluPHP::I()->lang($permission_name)]);
        }
        if (strpos($permission_key, 'grant_')===0){
            return YiluPHP::I()->lang('grant_permission_name', ['name'=>YiluPHP::I()->lang($permission_name)]);
        }
        return YiluPHP::I()->lang($permission_name);
    }

    /**
     * @name 添加应用权限
     * @desc
     * @param array $data 权限信息
     * @param integer $uid 默认授予此用户权限
     * @return boolean
     * @throws
     */
    public function add_permission($data, $uid=0)
    {
        if(false === model_permission::I()->insert_table($data)){
            unset($app, $data);
            return false;
        }
        if($uid && $permission = model_permission::I()->find_table(['app_id' => $data['app_id'],'permission_key' => $data['permission_key']], 'permission_id')){
            $permission_id = model_user_permission::I()->insert_table([
                'uid' => $uid,
                'permission_id' => $permission['permission_id'],
            ]);
        }
        $data['is_fixed'] = 1;
        $data['permission_key'] = 'grant_'.$data['permission_key'];
        if(false === model_permission::I()->insert_table($data)){
            unset($app, $data);
            return false;
        }
        if($uid && $permission = model_permission::I()->find_table(['app_id' => $data['app_id'],'permission_key' => $data['permission_key']], 'permission_id')){
            $permission_id = model_user_permission::I()->insert_table([
                'uid' => $uid,
                'permission_id' => $permission['permission_id'],
            ]);
        }
        $data['permission_key'] = 'grant_'.$data['permission_key'];
        if(false === model_permission::I()->insert_table($data)){
            unset($app, $data);
            return false;
        }
        if($uid && $permission = model_permission::I()->find_table(['app_id' => $data['app_id'],'permission_key' => $data['permission_key']], 'permission_id')){
            $permission_id = model_user_permission::I()->insert_table([
                'uid' => $uid,
                'permission_id' => $permission['permission_id'],
            ]);
        }

        unset($app, $data);
        return true;
    }

    /**
     * @name 添加应用权限
     * @desc
     * @param string $app_id 应用ID
     * @param string $permission_key 权限键名
     * @param array $data 权限信息
     * @return boolean
     * @throws
     */
    public function update_permission($app_id, $permission_key, $data)
    {
        $where = [
            'app_id' => $app_id,
            'permission_key' => 'grant_grant_'.$permission_key,
        ];
        $tmp_data = [];
        if (isset($data['permission_name'])){
            $tmp_data['permission_name'] = $data['permission_name'];
        }
        if (isset($data['description'])){
            $tmp_data['description'] = $data['description'];
        }
        if (count($tmp_data)==0){
            return true;
        }
        if(false === model_permission::I()->update_table($where,$tmp_data)){
            unset($app_id, $permission_key, $data, $where, $tmp_data);
            return false;
        }

        $where = [
            'app_id' => $app_id,
            'permission_key' => 'grant_'.$permission_key,
        ];
        if(false === model_permission::I()->update_table($where,$tmp_data)){
            unset($app_id, $permission_key, $data, $where, $tmp_data);
            return false;
        }

        $where = [
            'app_id' => $app_id,
            'permission_key' => $permission_key,
        ];
        if(false === model_permission::I()->update_table($where,$tmp_data)){
            unset($app_id, $permission_key, $data, $where, $tmp_data);
            return false;
        }
        unset($app_id, $permission_key, $data, $where, $tmp_data);
        return true;
    }

    /**
     * @name 删除应用权限
     * @desc
     * @param integer $permission_id 权限ID
     * @param string $app_id 权限所属应用
     * @param string $permission_key 权限键名
     * @return boolean
     * @throws
     */
    public function delete_permission($permission_id, $app_id, $permission_key)
    {
        if($tmp = model_permission::I()->find_table(['app_id'=>$app_id, 'permission_key'=>'grant_grant_'.$permission_key], 'permission_id')){
            //删除（包含该权限的）用户权限
            model_user_permission::I()->destroy(['permission_id' => $tmp['permission_id']]);
            //删除（包含该权限的）角色权限
            model_role_permission::I()->destroy(['permission_id' => $tmp['permission_id']]);
            //删除应用的权限
            model_permission::I()->delete(['permission_id' => $tmp['permission_id']]);
        }
        if($tmp = model_permission::I()->find_table(['app_id'=>$app_id, 'permission_key'=>'grant_'.$permission_key], 'permission_id')){
            //删除（包含该权限的）用户权限
            model_user_permission::I()->destroy(['permission_id' => $tmp['permission_id']]);
            //删除（包含该权限的）角色权限
            model_role_permission::I()->destroy(['permission_id' => $tmp['permission_id']]);
            //删除应用的权限
            model_permission::I()->delete(['permission_id' => $tmp['permission_id']]);
        }
        //删除（包含该权限的）用户权限
        model_user_permission::I()->destroy(['permission_id' => $permission_id]);
        //删除（包含该权限的）角色权限
        model_role_permission::I()->destroy(['permission_id' => $permission_id]);
        //删除应用的权限
        model_permission::I()->delete(['permission_id' => $permission_id]);
        unset($app, $permission_id,$tmp);
        return true;
    }

}
