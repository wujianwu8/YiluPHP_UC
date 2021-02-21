<?php
/*
 * 角色-权限模型类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 21:56
 */

class model_role_permission extends model
{
    protected $_table = 'role_permission';

    /**
     * @name 获取角色拥有的权限
     * @desc
     * @param integer $role_id 角色ID
     * @param string $app_id 应用ID
     * @return array 数据列表
     */
    public function select_role_permission($role_id, $app_id=null){
        if ($app_id){
            $sql = 'SELECT p.permission_id FROM role_permission AS rp, permission AS p 
                    WHERE rp.role_id=:role_id AND rp.permission_id=p.permission_id AND p.app_id=:app_id';
        }
        else{
            $sql = 'SELECT permission_id FROM role_permission WHERE role_id=:role_id';
        }
        $connection = $this->sub_connection();
        $stmt = mysql::I($connection)->prepare($sql);
        $stmt->bindValue(':role_id', $role_id, PDO::PARAM_INT);
        if ($app_id) {
            $stmt->bindValue(':app_id', $app_id, PDO::PARAM_STR);
        }
        $stmt->execute();
        unset($role_id, $app_id, $sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * @name 插入一条角色的权限
     * @desc
     * @param integer $role_id 角色ID
     * @param integer $permission_id 权限ID
     * @return boolean
     */
    public function insert_role_permission($role_id, $permission_id){
        $sql = 'INSERT INTO role_permission (role_id,permission_id) VALUES(:role_id,:permission_id) ON DUPLICATE KEY UPDATE role_id=:role_id2';
        $connection = $this->sub_connection();
        $stmt = mysql::I($connection)->prepare($sql);
        $stmt->bindValue(':role_id', $role_id, PDO::PARAM_INT);
        $stmt->bindValue(':role_id2', $role_id, PDO::PARAM_INT);
        $stmt->bindValue(':permission_id', $permission_id, PDO::PARAM_INT);
        $stmt->execute();
        unset($role_id, $permission_id, $sql);
        return mysql::I($connection)->lastInsertId();
    }

    /**
     * @name 获取角色包含的所有应用ID
     * @desc
     * @param integer $role_id 角色ID
     * @return array
     * @throws
     */
    public function select_all_app_id_of_role($role_id){
        $sql = 'SELECT DISTINCT(p.app_id) FROM role_permission AS rp, permission AS p 
                WHERE rp.role_id=:role_id AND rp.permission_id=p.permission_id';

        $connection = $this->sub_connection();
        $stmt = mysql::I($connection)->prepare($sql);
        $stmt->bindValue(':role_id', $role_id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = array_column($data, 'app_id');
        unset($role_id, $stmt, $sql, $connection);
        return $data;
    }
}
