<?php
/*
 * 用户-权限模型类
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/09
 * Time: 21:56
 */

class model_user_permission extends model
{
    protected $_table = 'user_permission';

    /**
     * @name 获取用户已有的所有权限
     * @desc
     * @param integer $uid 用户ID
     * @param string $app_id 指定系统查询，默认查询所有系统的
     * @return array
     * @throws
     */
    public function select_permissions_user_already_has($uid, $app_id='')
    {
        if (empty($app_id)){
            $cache_key = REDIS_KEY_USER_PERMISSION.$uid;
        }
        else{
            $cache_key = REDIS_KEY_USER_PERMISSION.$uid.'_'.$app_id;
        }
        if($data = $GLOBALS['app']->redis()->get($cache_key)){
            unset($cache_key, $uid, $app_id);
            return json_decode($data, true);
        }

        $sql = 'SELECT CONCAT(app_id, ":" , permission_key) AS str FROM permission WHERE permission_id IN(
                SELECT permission_id FROM user_permission WHERE uid=:uid 
                UNION 
                SELECT rp.permission_id FROM user_role AS ur, role_permission AS rp WHERE ur.uid=:uid2 AND ur.role_id=rp.role_id
            ) ';
        if (!empty($app_id)){
            $sql .= ' AND app_id=:app_id ';
        }
        $sql .= ' ORDER BY app_id ASC';
        $connection = $this->sub_connection();
        $stmt = $GLOBALS['app']->mysql($connection)->prepare($sql);
        $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindValue(':uid2', $uid, PDO::PARAM_INT);
        if (!empty($app_id)){
            $stmt->bindValue(':app_id', $app_id, PDO::PARAM_STR);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = array_column($data, 'str');
        $GLOBALS['app']->redis()->set($cache_key, json_encode($data));
        $GLOBALS['app']->redis()->expire($cache_key, TIME_10_MIN);
        unset($cache_key, $uid, $app_id, $stmt, $sql, $connection);
        return $data;
    }

    /**
     * @name 检查用户是否拥有某项权限
     * @desc
     * @param integer $uid 用户ID
     * @param string $permission_key 权限键名
     * @param string $app_id 系统ID
     * @return boolean true表示已有此权限，false则是没有此权限
     * @throws
     */
    public function if_has_permission($uid, $permission_key, $app_id)
    {
        return false !== array_search($app_id.':'.$permission_key, $this->select_permissions_user_already_has($uid, $app_id));
    }

}
