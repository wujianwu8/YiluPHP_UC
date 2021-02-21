<?php
/*
 * 用户反馈模型类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 23:16
 */

class model_user_feedback extends model
{
    protected $_table = 'user_feedback';

    /**
     * @name 读取反馈信息
     * @desc 分页读取
     * @param array $where  status投诉状态：0新反馈、2正在处理、1已处理，
     *                      keyword用于在title和content中搜索的关键字,
     *                      uids反馈人用户ID数组
     * @param integer $page 页码
     * @param integer $page_size 每页读取条数
     * @return array 数据列表
     */
    function paging_select_user_feedback(array $where, $page, $page_size){
        $sql = 'FROM '.$this->get_table();
        $where_tmp = [];
        $params = [];
        if (isset($where['status']) && $where['status']!==null){
            $where_tmp[] = ' `status`=:status ';
            $params[':status'] = $where['status'];
        }
        if (isset($where['keyword']) && $where['keyword']!==null){
            $where_tmp[] = ' (`title` LIKE :title || `content` LIKE :content) ';
            $params[':title'] = '%'.$where['keyword'].'%';
            $params[':content'] = '%'.$where['keyword'].'%';
        }

        if (!empty($where['uids'])){
            $keys = ':uid_'.(implode(',:uid_', array_keys($where['uids'])));
            $where_tmp[] = ' `uid` IN ('.$keys.') ';
            $params = array_merge($params, array_combine(explode(',', $keys), $where['uids']));
        }
        if ($where){
            $sql .= ' WHERE '.implode(' AND ', $where_tmp);
        }

        //读取总数量
        $stmt = mysql::I()->prepare('SELECT COUNT(1) AS c '.$sql);
        $stmt->execute($params);
        $count = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql .= ' ORDER BY ctime DESC LIMIT :start, :page_size';

        $start = ($page-1)*$page_size;
        $start<0 && $start = 0;
        $stmt = mysql::I()->prepare('SELECT * '.$sql);
        //第三个参数data_type，使用 PDO::PARAM_* 常量明确地指定参数的类型，如：
        //PDO::PARAM_INT、PDO::PARAM_STR、PDO::PARAM_BOOL、PDO::PARAM_NULL
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':page_size', $page_size, PDO::PARAM_INT);
        foreach ($params as $key => $value){
            //第三个参数data_type，使用 PDO::PARAM_* 常量明确地指定参数的类型，如：
            //PDO::PARAM_INT、PDO::PARAM_STR、PDO::PARAM_BOOL、PDO::PARAM_NULL
            $stmt->bindValue($key, $value, is_numeric($value)?PDO::PARAM_INT:(is_string($value)?PDO::PARAM_STR:
                (is_bool($value)?PDO::PARAM_BOOL:(is_null($value)?PDO::PARAM_NULL:PDO::PARAM_STR))));
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        unset($where, $page, $page_size, $sql, $start, $stmt);
        return [
            'count' => $count['c'],
            'data' => $data,
        ];
    }
}
