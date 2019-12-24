<?php
/*
 * 角色类
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/11
 * Time: 21:50
 */


class logic_role
{
	public function __construct()
	{
	}

	public function __destruct()
	{
	}

    /**
     * @name 删除角色
     * @desc
     * @param integer $role_id 角色ID
     * @return boolean
     * @throws
     */
    public function delete_role($role_id)
    {
        global $app;
        if (false === $app->model_user_role->destroy([
                'role_id' => $role_id
            ])){
            return false;
        }
        if (false === $app->model_role->delete([
                'id' => $role_id
            ])){
            return false;
        }
        return true;
    }
}
