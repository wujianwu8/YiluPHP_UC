<?php
/*
 * 语言包的翻译内容模型类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 20:16
 */

class model_language_value extends model
{
    protected $_table = 'language_value';

    /**
     * @name 插入或更新一条语言内容
     * @desc
     * @param array $data 语言数据
     * @return integer
     */
    public function insert_language_value($data){
        $keys = array_keys($data);
        $key_str = implode(',', $keys);
        $value_key_str = ':'.implode(',:', $keys);

        $sql = 'INSERT INTO '.$this->get_table().' ('.$key_str.') VALUES('.$value_key_str.') 
                ON DUPLICATE KEY UPDATE ';
        $sql_arr = [];
        if (isset($data['language_value'])){
            $sql_arr[] = ' language_value=:language_value2';
            $data['language_value2'] = $data['language_value'];
        }
        if (isset($data['output_type'])){
            $sql_arr[] = ' output_type=:output_type2';
            $data['output_type2'] = $data['output_type'];
        }
        if (!$sql_arr) {
            $sql_arr[] = ' ctime=ctime';
        }
        $sql .= implode(',', $sql_arr);
        unset($sql_arr);
        $connection = $this->sub_connection();
        $stmt = mysql::I($connection)->prepare($sql);
        foreach($data as $key => $value){
            $stmt->bindValue(':'.$key, $value, is_numeric($value)?PDO::PARAM_INT:(is_string($value)?PDO::PARAM_STR:
                (is_bool($value)?PDO::PARAM_BOOL:(is_null($value)?PDO::PARAM_NULL:PDO::PARAM_STR))));
        }
        $stmt->execute();
        unset($data, $sql, $stmt, $keys, $key_str, $value_key_str);
        return mysql::I($connection)->lastInsertId();
    }

    /**
     * @name 获取项目的去重后的、已排序好的语言键名
     * @desc
     * @param string $project_key 语言数据
     * @param string $keyword 搜索关键字
     * @param integer $page 页码
     * @param integer $page_size 每页数据量
     * @return array
     */
    public function paging_select_project_distinct_language_key($project_key, $page, $page_size, $keyword=null){
        $start = ($page-1)*$page_size;
        $sql = 'SELECT DISTINCT(language_key) language_key FROM '.$this->get_table()
                .' WHERE project_key=:project_key ';
        if ($keyword){
            $sql .= ' AND (language_key LIKE :language_key OR language_value LIKE :language_value) ';
        }
        $sql .= ' ORDER BY language_key ASC LIMIT :start, :page_size';
        $connection = $this->sub_connection();
        $stmt = mysql::I($connection)->prepare($sql);
        $stmt->bindValue(':project_key', $project_key, PDO::PARAM_STR);
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':page_size', $page_size, PDO::PARAM_INT);
        if ($keyword){
            $stmt->bindValue(':language_key', '%'.$keyword.'%', PDO::PARAM_STR);
            $stmt->bindValue(':language_value', '%'.$keyword.'%', PDO::PARAM_STR);
        }
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        unset($project_key, $page, $page_size, $sql, $stmt, $start);
        return $res;
    }

    /**
     * @name 获取项目的去重后的语言键名总数量
     * @desc
     * @param string $project_key 语言数据
     * @param string $keyword 搜索关键字
     * @return integer
     */
    public function count_project_distinct_language_key($project_key, $keyword=null){
        $sql = 'SELECT COUNT(DISTINCT language_key) AS c FROM '.$this->get_table().' WHERE project_key=:project_key ';
        if ($keyword){
            $sql .= ' AND (language_key LIKE :language_key OR language_value LIKE :language_value)';
        }
        $connection = $this->sub_connection();
        $stmt = mysql::I($connection)->prepare($sql);
        $stmt->bindValue(':project_key', $project_key, PDO::PARAM_STR);
        if ($keyword){
            $stmt->bindValue(':language_key', '%'.$keyword.'%', PDO::PARAM_STR);
            $stmt->bindValue(':language_value', '%'.$keyword.'%', PDO::PARAM_STR);
        }
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        unset($project_key,$sql, $stmt);
        return $res['c'];
    }
}
