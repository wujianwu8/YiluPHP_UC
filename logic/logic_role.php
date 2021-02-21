<?php
/*
 * 角色类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 21:50
 */


class logic_role extends base_class
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
        if (false === model_user_role::I()->destroy([
                'role_id' => $role_id
            ])){
            return false;
        }
        if (false === model_role::I()->delete([
                'id' => $role_id
            ])){
            return false;
        }
        return true;
    }
}
