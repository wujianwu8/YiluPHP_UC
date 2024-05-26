<?php
/**
 * 权限模型类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * Date: 2021/01/23
 * Time: 21:56
 */

class model_permission extends model
{
    protected $_table = 'permission';
    private $_permission_control_keys = [
        'view_permission'               => 'lang_view_permission',
        'add_permission'                => 'lang_add_permission',
        'edit_permission'               => 'lang_edit_permission',
        'delete_permission'             => 'lang_delete_permission',
        'grant_view_permission'         => 'lang_view_permission',
        'grant_add_permission'          => 'lang_add_permission',
        'grant_edit_permission'         => 'lang_edit_permission',
        'grant_delete_permission'       => 'lang_delete_permission',
        'grant_grant_view_permission'   => 'lang_view_permission',
        'grant_grant_add_permission'    => 'lang_add_permission',
        'grant_grant_edit_permission'   => 'lang_edit_permission',
        'grant_grant_delete_permission' => 'lang_delete_permission',
    ];

    public function permission_control_keys()
    {
        return $this->_permission_control_keys;
    }

    /**
     * @name 获取指定用户权限至少一项权限的应用ID
     * @desc
     * @param integer $uid 用户ID
     * @return array 数据列表
     */
    public function select_user_have_permission_app_id($uid)
    {
        $sql = 'SELECT DISTINCT(p.app_id) app_id FROM permission AS p WHERE p.permission_id IN (
                SELECT permission_id FROM user_permission WHERE uid=:uid1
            ) OR p.permission_id IN (
                SELECT rp.permission_id FROM user_role AS ur, role_permission AS rp WHERE ur.uid=:uid2 AND ur.role_id=rp.role_id
            )';
        $connection = $this->sub_connection();
        $stmt = mysql::I($connection)->prepare($sql);
        $stmt->bindValue(':uid1', $uid, PDO::PARAM_INT);
        $stmt->bindValue(':uid2', $uid, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @name 获取指定角色至少一项权限的应用ID
     * @desc
     * @param integer $role_id 角色ID
     * @return array 数据列表
     */
    public function select_role_have_permission_app_id($role_id)
    {
        $sql = 'SELECT DISTINCT(p.app_id) app_id FROM permission AS p WHERE p.permission_id IN (
                SELECT permission_id FROM role_permission WHERE role_id=:role_id
            )';
        $connection = $this->sub_connection();
        $stmt = mysql::I($connection)->prepare($sql);
        $stmt->bindValue(':role_id', $role_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
