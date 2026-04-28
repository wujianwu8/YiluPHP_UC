<?php
/**
 * 邀请链接模型类
 */
class model_invitation_link extends model
{
    protected $_table = 'invitation_link';

    /**
     * @name 根据邀请码查找邀请链接
     * @desc
     * @param string $invite_code 邀请码
     * @return array|false
     */
    public function find_by_invite_code(string $invite_code)
    {
        return $this->find_table(['invite_code' => $invite_code]);
    }

    /**
     * @name 根据用户ID和场景查找邀请链接
     * @desc
     * @param integer $uid 用户ID
     * @param string $scene 场景
     * @return array|false
     */
    public function find_by_uid_and_scene(int $uid, string $scene)
    {
        return $this->find_table(['uid' => $uid, 'scene' => $scene]);
    }

    /**
     * @name 根据用户ID读取邀请链接
     * @desc
     * @param integer $uid 用户ID
     * @param string $scene 场景，可选
     * @return array
     */
    public function select_by_uid(int $uid, string $scene = '')
    {
        $where = ['uid' => $uid];
        if ($scene !== '') {
            $where['scene'] = $scene;
        }
        return $this->select_all($where, 'ctime DESC');
    }

    /**
     * @name 统计邀请链接数量
     * @desc
     * @param array $where 查询条件
     * @return integer
     */
    public function count_links(array $where = [])
    {
        $table_name = $this->sub_table();
        $connection = $this->sub_connection();
        $sql = 'SELECT COUNT(1) AS total FROM `' . $table_name . '`';
        $where_sql = [];
        $params = [];
        if (isset($where['uid'])) {
            $where_sql[] = 'uid = :uid';
            $params[':uid'] = $where['uid'];
        }
        if (isset($where['scene']) && $where['scene'] !== '') {
            $where_sql[] = 'scene = :scene';
            $params[':scene'] = $where['scene'];
        }
        if (isset($where['invite_code']) && $where['invite_code'] !== '') {
            $where_sql[] = 'invite_code LIKE :invite_code';
            $params[':invite_code'] = '%' . $where['invite_code'] . '%';
        }
        if (isset($where['remark']) && $where['remark'] !== '') {
            $where_sql[] = 'remark LIKE :remark';
            $params[':remark'] = '%' . $where['remark'] . '%';
        }
        if ($where_sql) {
            $sql .= ' WHERE ' . implode(' AND ', $where_sql);
        }
        $stmt = mysql::I($connection)->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return empty($result['total']) ? 0 : intval($result['total']);
    }

    /**
     * @name 分页读取邀请链接
     * @desc
     * @param array $where 查询条件
     * @param integer $page 页码
     * @param integer $page_size 每页条数
     * @return array
     */
    public function paging_select_links(array $where = [], int $page = 1, int $page_size = 10)
    {
        $table_name = $this->sub_table();
        $connection = $this->sub_connection();
        $sql = 'SELECT * FROM `' . $table_name . '`';
        $where_sql = [];
        $params = [];
        if (isset($where['uid'])) {
            $where_sql[] = 'uid = :uid';
            $params[':uid'] = $where['uid'];
        }
        if (isset($where['scene']) && $where['scene'] !== '') {
            $where_sql[] = 'scene = :scene';
            $params[':scene'] = $where['scene'];
        }
        if (isset($where['invite_code']) && $where['invite_code'] !== '') {
            $where_sql[] = 'invite_code LIKE :invite_code';
            $params[':invite_code'] = '%' . $where['invite_code'] . '%';
        }
        if (isset($where['remark']) && $where['remark'] !== '') {
            $where_sql[] = 'remark LIKE :remark';
            $params[':remark'] = '%' . $where['remark'] . '%';
        }
        if ($where_sql) {
            $sql .= ' WHERE ' . implode(' AND ', $where_sql);
        }
        $sql .= ' ORDER BY id DESC LIMIT :start, :page_size';
        $start = ($page - 1) * $page_size;
        $start < 0 && $start = 0;
        $stmt = mysql::I($connection)->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':page_size', $page_size, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
