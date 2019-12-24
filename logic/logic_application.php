<?php
/*
 * 应用逻辑处理类
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/09
 * Time: 21:39
 */

class logic_application
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
        global $app;
        //查找出应用的所有权限
        if($permission_ids = $app->model_permission->select_all(['app_id'=>$app_id], 'permission_id')) {
            $permission_ids = array_column($permission_ids, 'permission_id');
            //删除（包含该应用的权限的）用户权限
            $app->model_user_permission->destroy(['permission_id' => [
                'symbol' => 'IN',
                'value' => $permission_ids,
            ]]);
            //删除（包含该应用的权限的）角色权限
            $app->model_role_permission->destroy(['permission_id' => [
                'symbol' => 'IN',
                'value' => $permission_ids,
            ]]);
            //删除应用的权限
            $app->model_permission->destroy(['app_id' => $app_id]);
        }
        //删除应用信息
        $app->model_application->delete(['app_id'=>$app_id]);
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
        global $app;
        if (strpos($permission_key, 'grant_grant_')===0){
            return $app->lang('grant_for_grant_permission_name', ['name'=>$app->lang($permission_name)]);
        }
        if (strpos($permission_key, 'grant_')===0){
            return $app->lang('grant_permission_name', ['name'=>$app->lang($permission_name)]);
        }
        return $app->lang($permission_name);
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
        global $app;
        if(false === $app->model_permission->insert_table($data)){
            unset($app, $data);
            return false;
        }
        if($uid && $permission = $app->model_permission->find_table(['app_id' => $data['app_id'],'permission_key' => $data['permission_key']], 'permission_id')){
            $permission_id = $app->model_user_permission->insert_table([
                'uid' => $uid,
                'permission_id' => $permission['permission_id'],
            ]);
        }
        $data['is_fixed'] = 1;
        $data['permission_key'] = 'grant_'.$data['permission_key'];
        if(false === $app->model_permission->insert_table($data)){
            unset($app, $data);
            return false;
        }
        if($uid && $permission = $app->model_permission->find_table(['app_id' => $data['app_id'],'permission_key' => $data['permission_key']], 'permission_id')){
            $permission_id = $app->model_user_permission->insert_table([
                'uid' => $uid,
                'permission_id' => $permission['permission_id'],
            ]);
        }
        $data['permission_key'] = 'grant_'.$data['permission_key'];
        if(false === $app->model_permission->insert_table($data)){
            unset($app, $data);
            return false;
        }
        if($uid && $permission = $app->model_permission->find_table(['app_id' => $data['app_id'],'permission_key' => $data['permission_key']], 'permission_id')){
            $permission_id = $app->model_user_permission->insert_table([
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
        global $app;
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
        if(false === $app->model_permission->update_table($where,$tmp_data)){
            unset($app_id, $permission_key, $data, $where, $tmp_data);
            return false;
        }

        $where = [
            'app_id' => $app_id,
            'permission_key' => 'grant_'.$permission_key,
        ];
        if(false === $app->model_permission->update_table($where,$tmp_data)){
            unset($app_id, $permission_key, $data, $where, $tmp_data);
            return false;
        }

        $where = [
            'app_id' => $app_id,
            'permission_key' => $permission_key,
        ];
        if(false === $app->model_permission->update_table($where,$tmp_data)){
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
        global $app;
        if($tmp = $app->model_permission->find_table(['app_id'=>$app_id, 'permission_key'=>'grant_grant_'.$permission_key], 'permission_id')){
            //删除（包含该权限的）用户权限
            $app->model_user_permission->destroy(['permission_id' => $tmp['permission_id']]);
            //删除（包含该权限的）角色权限
            $app->model_role_permission->destroy(['permission_id' => $tmp['permission_id']]);
            //删除应用的权限
            $app->model_permission->delete(['permission_id' => $tmp['permission_id']]);
        }
        if($tmp = $app->model_permission->find_table(['app_id'=>$app_id, 'permission_key'=>'grant_'.$permission_key], 'permission_id')){
            //删除（包含该权限的）用户权限
            $app->model_user_permission->destroy(['permission_id' => $tmp['permission_id']]);
            //删除（包含该权限的）角色权限
            $app->model_role_permission->destroy(['permission_id' => $tmp['permission_id']]);
            //删除应用的权限
            $app->model_permission->delete(['permission_id' => $tmp['permission_id']]);
        }
        //删除（包含该权限的）用户权限
        $app->model_user_permission->destroy(['permission_id' => $permission_id]);
        //删除（包含该权限的）角色权限
        $app->model_role_permission->destroy(['permission_id' => $permission_id]);
        //删除应用的权限
        $app->model_permission->delete(['permission_id' => $permission_id]);
        unset($app, $permission_id,$tmp);
        return true;
    }

}
