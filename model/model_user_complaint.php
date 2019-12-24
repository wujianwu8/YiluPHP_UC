<?php
/*
 * 投诉用户模型类
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/03
 * Time: 23:16
 */

class model_user_complaint extends model
{
    protected $_table = 'user_complaint';

    /**
     * @name 读取投诉信息
     * @desc 分页读取
     * @param array $where  status投诉状态：0新投诉、2正在处理、1已处理，
     *                      keyword用于在title和content中搜索的关键字,
     *                      complaint_uids投诉人用户ID数组,
     *                      respondent_uids被投诉人用户ID数组
     * @param integer $page 页码
     * @param integer $page_size 每页读取条数
     * @return array 数据列表
     */
    function paging_select_user_complaint(array $where, $page, $page_size){
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

        if (!empty($where['complaint_uids'])){
            $keys = ':complaint_uid_'.(implode(',:complaint_uid_', array_keys($where['complaint_uids'])));
            $where_tmp[] = ' `complaint_uid` IN ('.$keys.') ';
            $params = array_merge($params, array_combine(explode(',', $keys), $where['complaint_uids']));
        }
        if (!empty($where['respondent_uids'])){
            $keys = ':respondent_uid_'.(implode(',:respondent_uid_', array_keys($where['respondent_uids'])));
            $where_tmp[] = ' `respondent_uid` IN ('.$keys.') ';
            $params = array_merge($params, array_combine(explode(',', $keys), $where['respondent_uids']));
        }
        if ($where){
            $sql .= ' WHERE '.implode(' AND ', $where_tmp);
        }

        //读取总数量
        $stmt = $GLOBALS['app']->mysql()->prepare('SELECT COUNT(1) AS c '.$sql);
        $stmt->execute($params);
        $count = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql .= ' ORDER BY ctime DESC LIMIT :start, :page_size';

        $start = ($page-1)*$page_size;
        $start<0 && $start = 0;
        $stmt = $GLOBALS['app']->mysql()->prepare('SELECT * '.$sql);
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
