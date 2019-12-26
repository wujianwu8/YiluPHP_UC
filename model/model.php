<?php
/*
 * 数据模型的基类
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/06
 * Time: 21:23
 * 新增一条记录的方法名使用insert_开头
 * 新增多条记录的方法名使用add_开头
 * 新增(已存在则更新)一条记录的方法名使用save_开头
 * 新增(已存在则更新)多条记录的方法名使用store_开头
 * 删除一条记录的方法名使用delete_开头
 * 删除多条记录的方法名使用destroy_开头
 * 更新一条记录的方法名使用update_开头
 * 更新多条记录的方法名使用change_开头
 * 查询一条记录的方法名使用find_开头
 * 查询一条统计数据的方法名使用count_开头
 * 查询多条记录的方法名使用select_开头
 * 分页查询的方法名使用paging_开头
 */

class model
{
    //表名，不包含分表名
    protected $_table = null;
    //数据库连接名
    protected $_connection = 'default';
    //拆分表的方式，null表示不拆分，last_two_digits表示根据（如ID）末尾2位数拆分成100个表
    protected $_split_method = null;
    //用于分表的字段名
    protected $_split_by_field = null;

    public function __construct()
    {
        if (!empty($GLOBALS['config']['split_table']) && !empty($this->_split_method)){
            if (empty($this->_table)){
                throw new Exception('分表必须设置 $_table ,且不为空', CODE_ERROR_IN_MODEL);
            }
            if (empty($this->_connection)){
                throw new Exception('分表必须设置 $_connection ,且不为空', CODE_ERROR_IN_MODEL);
            }
            if (empty($this->_split_method)){
                throw new Exception('分表必须设置 $_split_method ,且不为空', CODE_ERROR_IN_MODEL);
            }
            if (empty($this->_split_by_field)){
                throw new Exception('分表必须设置 $_split_by_field ,且不为空', CODE_ERROR_IN_MODEL);
            }
        }
    }

    public function get_table(){
        return $this->_table;
    }

    public function get_connection(){
        return $this->_connection;
    }

    public function get_split_by_field(){
        return $this->_split_by_field;
    }

    /**
     * @name 获取分表名
     * @desc 分表所在的数据库连接名与分表名的后缀需要保持一致
     * @param string $field_value 用于分表的字段的值
     * @return string 分表名
     */
	public function sub_table($field_value=null)
	{
        if (empty($GLOBALS['config']['split_table']) || empty($this->_split_method)){
            return $this->_table;
        }
	    if ($field_value===null){
            return $this->_table;
        }
	    $suffix = $this->split_suffix($field_value);
	    if($suffix!==''){
            unset($field_value);
	        return $this->_table . '_'.$suffix;
        }
        unset($suffix, $field_value);
        return $this->_table;
	}

    /**
     * @name 获取分表的库连接名
     * @desc 分表所在的数据库连接名与分表名的后缀需要保持一致，数据库连接名是指在配置文件中用户自定的库连接名，默认库连接名为default
     * @param string $field_value 用于分表的字段的值
     * @return string 分表所在的数据库连接名
     */
    public function sub_connection($field_value=null)
    {
        if (empty($GLOBALS['config']['split_table']) || empty($this->_split_method)){
            return $this->_connection;
        }
        if ($field_value===null){
            return $this->_connection;
        }
        $suffix = $this->split_suffix($field_value);
        if($suffix!==''){
            $tmp = $this->_connection . '_'.$suffix;
            if(isset($GLOBALS['config']['mysql'][$tmp])){
                return $tmp;
            }
        }
        unset($suffix, $field_value);
        return $this->_connection;
    }

    /**
     * @name 获取分表的后缀
     * @desc
     * @param string $field_value 用于分表的字段的值
     * @return string 用于分表的后缀
     */
    public function split_suffix($field_value=null)
    {
        if (empty($GLOBALS['config']['split_table']) || empty($this->_split_method)){
            return '';
        }
        if ($field_value===null){
            return '';
        }
        //根据（如ID）末尾2位数拆分成100个表
        if (strlen($field_value)>0 && $this->_split_method=='last_two_digits'){
            return intval(substr($field_value, -2, 2));
        }
        return '';
    }

    /**
     * @name 从数据表中查询指定条件的数据数量
     * @desc
     * @param array $where 查询条件，多个条件之间是并且的关系
     * @param string $field_value 用于分表的字段的值
     * @param string $extend_sql 延伸的SQL语句，主要用于补充where条件
     * @param array $extend_params 延伸的SQL参数及其值，主要用于给延伸的SQL语句赋值参数值
     * @return array 数据列表
     */
    function count($where, $field_value=null, string $extend_sql='', array $extend_params=[])
    {
        $table_name = $this->sub_table($field_value);
        $connection = $this->sub_connection($field_value);

        $sql = 'SELECT COUNT(1) AS c FROM `'.$table_name.'`';
        $arr = [];
        foreach ($where as $key => $value) {
            if(is_array($value)){
                if(is_array($value['value'])){
                    $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                    $arr[] = ' `'.$key.'` '.$value['symbol'].' ('.$plist.') ';
                }
                else{
                    $arr[] = ' `'.$key.'` '.$value['symbol'].' :'.$key;
                }
            }
            else{
                $arr[] = ' `'.$key.'`=:'.$key;
            }
        }
        $where && $sql .= ' WHERE ';
        $sql .= implode(' AND ', $arr) . $extend_sql;
        try {
            $stmt = $GLOBALS['app']->mysql($connection)->prepare($sql);
            $where = array_merge($where, $extend_params);
            foreach ($where as $key => &$value) {
                $direct_assign = true;
                if(is_array($value)){
                    if(is_array($value['value'])){
                        $direct_assign = false;
                        $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                        $params = array_combine(explode(",", $plist), $value['value']);
                        foreach($params as $key2 => $param){
                            $stmt->bindValue($key2, $param, is_numeric($param)?PDO::PARAM_INT:(is_string($param)?PDO::PARAM_STR:
                                (is_bool($param)?PDO::PARAM_BOOL:(is_null($param)?PDO::PARAM_NULL:PDO::PARAM_STR))));
                        }
                    }
                    else{
                        $val = $value['value'];
                    }
                }
                else{
                    $val = $value;
                }
                if($direct_assign) {
                    //第三个参数data_type，使用 PDO::PARAM_* 常量明确地指定参数的类型，如：
                    //PDO::PARAM_INT、PDO::PARAM_STR、PDO::PARAM_BOOL、PDO::PARAM_NULL
                    $stmt->bindValue(':'.$key, $val, is_numeric($val)?PDO::PARAM_INT:(is_string($val)?PDO::PARAM_STR:
                        (is_bool($val)?PDO::PARAM_BOOL:(is_null($val)?PDO::PARAM_NULL:PDO::PARAM_STR))));
                }
            }
            $stmt->execute();
//		PDO::FETCH_ASSOC          从结果集中获取以列名为索引的关联数组。
//  	PDO::FETCH_NUM             从结果集中获取一个以列在行中的数值偏移量为索引的值数组。
//  	PDO::FETCH_BOTH            这是默认值，包含上面两种数组。
//  	PDO::FETCH_OBJ               从结果集当前行的记录中获取其属性对应各个列名的一个对象。
//  	PDO::FETCH_BOUND        使用fetch()返回TRUE，并将获取的列值赋给在bindParm()方法中指 定的相应变量。
//  	PDO::FETCH_LAZY            创建关联数组和索引数组，以及包含列属性的一个对象，从而可以在这三种接口中任选一种。
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            unset($table_name, $connection, $field_value, $sql, $arr, $where, $stmt);
            return $res['c'];
        } catch (PDOException $e) {
            unset($table_name, $connection, $field_value, $sql, $arr, $where);
            //这里要写文件日志
            write_applog('ERROR', $e->getMessage());
            throw new Exception($e->getMessage(), CODE_DB_ERR);
        }
    }

    /**
     * @name 从数据表中查询出一页数据
     * @desc 分页读取
     * @param array $where 查询条件，多个条件之间是并且的关系
     * @param integer $page 页码
     * @param integer $page_size 每页读取条数
     * @param string $order_by 排序方式
     * @param string $fields 需要返回的字段
     * @param string $field_value 用于分表的字段的值
     * @param string $extend_sql 延伸的SQL语句，主要用于补充where条件
     * @param array $extend_params 延伸的SQL参数及其值，主要用于给延伸的SQL语句赋值参数值
     * @return array 数据列表
     */
    function paging_select(array $where, int $page, int $page_size, string $order_by='', string $fields='*',
                           string $field_value=null, string $extend_sql='', array $extend_params=[])
    {
        $table_name = $this->sub_table($field_value);
        $connection = $this->sub_connection($field_value);

        if(!preg_match("/^[\(\)\d\s\w-_,`]*$/",$order_by)){
            write_applog('ERROR', 'arguments $order_by is illegal: '.$order_by);
            throw new Exception('arguments $order_by is illegal: '.$order_by, CODE_ERROR_IN_MODEL);
        }
        if(!preg_match("/^[\(\)\d\s\w-_\`,\(\)\*]*$/",$fields)){
            write_applog('ERROR', 'arguments $fields is illegal: '.$fields);
            throw new Exception('arguments $fields is illegal: '.$fields, CODE_ERROR_IN_MODEL);
        }

        $sql = 'SELECT '.$fields.' FROM `'.$table_name.'`';
        $arr = [];
        foreach ($where as $key => $value) {
            if(is_array($value)){
                if(is_array($value['value'])){
                    $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                    $arr[] = ' `'.$key.'` '.$value['symbol'].' ('.$plist.') ';
                }
                else{
                    $arr[] = ' `'.$key.'` '.$value['symbol'].' :'.$key;
                }
            }
            else{
                $arr[] = ' `'.$key.'`=:'.$key;
            }
        }
        $where && $sql .= ' WHERE ';
        $sql .= implode(' AND ', $arr) . $extend_sql . (trim($order_by)!==''?' ORDER BY '.$order_by : '') .' LIMIT :start, :page_size ';
        $page = intval($page);
        $page_size = intval($page_size);
        $start = ($page-1)*$page_size;
        $start<0 && $start = 0;
        try {
            $stmt = $GLOBALS['app']->mysql($connection)->prepare($sql);
            $where = array_merge($where, $extend_params);
            foreach ($where as $key => &$value) {
                $direct_assign = true;
                if(is_array($value)){
                    if(is_array($value['value'])){
                        $direct_assign = false;
                        $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                        $params = array_combine(explode(",", $plist), $value['value']);
                        foreach($params as $key2 => $param){
                            $stmt->bindValue($key2, $param, is_numeric($param)?PDO::PARAM_INT:(is_string($param)?PDO::PARAM_STR:
                                (is_bool($param)?PDO::PARAM_BOOL:(is_null($param)?PDO::PARAM_NULL:PDO::PARAM_STR))));
                        }
                    }
                    else{
                        $val = $value['value'];
                    }
                }
                else{
                    $val = $value;
                }
                if($direct_assign) {
                    //第三个参数data_type，使用 PDO::PARAM_* 常量明确地指定参数的类型，如：
                    //PDO::PARAM_INT、PDO::PARAM_STR、PDO::PARAM_BOOL、PDO::PARAM_NULL
                    $stmt->bindValue(':' . $key, $val, is_numeric($val) ? PDO::PARAM_INT : (is_string($val) ? PDO::PARAM_STR :
                        (is_bool($val) ? PDO::PARAM_BOOL : (is_null($val) ? PDO::PARAM_NULL : PDO::PARAM_STR))));
                }
            }
            $stmt->bindValue(':start', $start, PDO::PARAM_INT);
            $stmt->bindValue(':page_size', $page_size, PDO::PARAM_INT);
            $stmt->execute();
//		PDO::FETCH_ASSOC          从结果集中获取以列名为索引的关联数组。
//  	PDO::FETCH_NUM             从结果集中获取一个以列在行中的数值偏移量为索引的值数组。
//  	PDO::FETCH_BOTH            这是默认值，包含上面两种数组。
//  	PDO::FETCH_OBJ               从结果集当前行的记录中获取其属性对应各个列名的一个对象。
//  	PDO::FETCH_BOUND        使用fetch()返回TRUE，并将获取的列值赋给在bindParm()方法中指 定的相应变量。
//  	PDO::FETCH_LAZY            创建关联数组和索引数组，以及包含列属性的一个对象，从而可以在这三种接口中任选一种。
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            unset($where, $page, $page_size, $order_by, $fields, $field_value, $table_name, $connection, $sql, $arr, $start, $stmt);
            return $res;
        } catch (PDOException $e) {
            unset($where, $page, $page_size, $order_by, $fields, $field_value, $table_name, $connection, $sql, $arr, $start);
            //这里要写文件日志
            write_applog('ERROR', $e->getMessage());
            throw new Exception($e->getMessage(), CODE_DB_ERR);
        }
    }

    /**
     * @name 从数据表中查询出所有符合条件的数据
     * @desc
     * @param array $where 查询条件，多个条件之间是并且的关系
     * @param string $order_by 排序方式
     * @param string $fields 需要读取的字段，默认为全部字段*
     * @param string $field_value 用于分表的字段的值
     * @param string $extend_sql 延伸的SQL语句，主要用于补充where条件
     * @param array $extend_params 延伸的SQL参数及其值，主要用于给延伸的SQL语句赋值参数值
     * @return array 数据列表
     */
    function select_all($where, $order_by='', $fields='*', $field_value=null, string $extend_sql='', array $extend_params=[])
    {
        $table_name = $this->sub_table($field_value);
        $connection = $this->sub_connection($field_value);

        if(!preg_match("/^[\(\)\d\s\w-_,`]*$/",$order_by)){
            write_applog('ERROR', 'arguments $order_by is illegal: '.$order_by);
            throw new Exception('arguments $order_by is illegal: '.$order_by, CODE_ERROR_IN_MODEL);
        }
        if(!preg_match("/^[\(\)\d\s\w-_,`\(\)\*]*$/",$fields)){
            write_applog('ERROR', 'arguments $fields is illegal: '.$fields);
            throw new Exception('arguments $fields is illegal: '.$fields, CODE_ERROR_IN_MODEL);
        }

        $sql = 'SELECT '.$fields.' FROM `'.$table_name.'`';
        $arr = [];
        foreach ($where as $key => $value) {
            if(is_array($value)){
                if(is_array($value['value'])){
                    $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                    $arr[] = ' `'.$key.'` '.$value['symbol'].' ('.$plist.') ';
                }
                else{
                    $arr[] = ' `'.$key.'` '.$value['symbol'].' :'.$key;
                }
            }
            else{
                $arr[] = ' `'.$key.'`=:'.$key;
            }
        }
        $where && $sql .= ' WHERE ';
        $sql .= implode(' AND ', $arr) . $extend_sql . (trim($order_by)!==''?' ORDER BY '.$order_by : '');
        try {
            $stmt = $GLOBALS['app']->mysql($connection)->prepare($sql);
            $where = array_merge($where, $extend_params);
            foreach ($where as $key => &$value) {
                $direct_assign = true;
                if(is_array($value)){
                    if(is_array($value['value'])){
                        $direct_assign = false;
                        $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                        $params = array_combine(explode(",", $plist), $value['value']);
                        foreach($params as $key2 => $param){
                            $stmt->bindValue($key2, $param, is_numeric($param)?PDO::PARAM_INT:(is_string($param)?PDO::PARAM_STR:
                                (is_bool($param)?PDO::PARAM_BOOL:(is_null($param)?PDO::PARAM_NULL:PDO::PARAM_STR))));
                        }
                    }
                    else{
                        $val = $value['value'];
                    }
                }
                else{
                    $val = $value;
                }
                if($direct_assign) {
                    //第三个参数data_type，使用 PDO::PARAM_* 常量明确地指定参数的类型，如：
                    //PDO::PARAM_INT、PDO::PARAM_STR、PDO::PARAM_BOOL、PDO::PARAM_NULL
                    $stmt->bindValue(':'.$key, $val, is_numeric($val)?PDO::PARAM_INT:(is_string($val)?PDO::PARAM_STR:
                        (is_bool($val)?PDO::PARAM_BOOL:(is_null($val)?PDO::PARAM_NULL:PDO::PARAM_STR))));
                }
            }
            $stmt->execute();
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            unset($where, $order_by, $fields, $field_value, $table_name, $connection, $sql, $arr, $stmt);
            return $res;
        } catch (PDOException $e) {
            unset($where, $order_by, $fields, $field_value, $table_name, $connection, $sql, $arr);
            //这里要写文件日志
            write_applog('ERROR', $e->getMessage());
            throw new Exception($e->getMessage(), CODE_DB_ERR);
        }
    }

    /**
     * @name 从数据表中查询出一条数据
     * @desc
     * @param array $where 查询条件，多个条件之间是并且的关系
     * @param string $fields 需要读取的字段，默认为全部字段*
     * @param string $field_value 用于分表的字段的值
     * @param string $extend_sql 延伸的SQL语句，主要用于补充where条件
     * @param array $extend_params 延伸的SQL参数及其值，主要用于给延伸的SQL语句赋值参数值
     * @return array/boolean 有数据返回数组，没有返回false
     */
    function find_table($where, $fields='*', $field_value=null, string $extend_sql='', array $extend_params=[])
    {
        $table_name = $this->sub_table($field_value);
        $connection = $this->sub_connection($field_value);

        if($fields!=="*" && !preg_match("/^[\(\)\d\s\w-_,`]*$/",$fields)){
            write_applog('ERROR', 'arguments $fields is illegal: '.$fields);
            throw new Exception('arguments $fields is illegal: '.$fields, CODE_ERROR_IN_MODEL);
        }
        if (!$where) {
            write_applog('ERROR', 'arguments $where is empty');
            throw new Exception('arguments $where is empty', CODE_ERROR_IN_MODEL);
        }
        if (!$table_name) {
            write_applog('ERROR', 'arguments $table_name is empty');
            throw new Exception('arguments $table_name is empty', CODE_ERROR_IN_MODEL);
        }

        $sql = 'SELECT '.$fields.' FROM `'.$table_name.'` WHERE ';
        $arr = [];
        foreach ($where as $key => $value) {
            if(is_array($value)){
                if(is_array($value['value'])){
                    $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                    $arr[] = ' `'.$key.'` '.$value['symbol'].' ('.$plist.') ';
                }
                else{
                    $arr[] = ' `'.$key.'` '.$value['symbol'].' :'.$key;
                }
            }
            else{
                $arr[] = ' `'.$key.'`=:'.$key;
            }
        }
        $sql .= implode(' AND ', $arr) . ' ' .$extend_sql . ' LIMIT 1 ';
        try {
            $stmt = $GLOBALS['app']->mysql($connection)->prepare($sql);
            $where = array_merge($where, $extend_params);
            foreach ($where as $key => &$value) {
                $direct_assign = true;
                if(is_array($value)){
                    if(is_array($value['value'])){
                        $direct_assign = false;
                        $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                        $params = array_combine(explode(",", $plist), $value['value']);
                        foreach($params as $key2 => $param){
                            $stmt->bindValue($key2, $param, is_numeric($param)?PDO::PARAM_INT:(is_string($param)?PDO::PARAM_STR:
                                (is_bool($param)?PDO::PARAM_BOOL:(is_null($param)?PDO::PARAM_NULL:PDO::PARAM_STR))));
                        }
                    }
                    else{
                        $val = $value['value'];
                    }
                }
                else{
                    $val = $value;
                }
                if($direct_assign) {
                    //第三个参数data_type，使用 PDO::PARAM_* 常量明确地指定参数的类型，如：
                    //PDO::PARAM_INT、PDO::PARAM_STR、PDO::PARAM_BOOL、PDO::PARAM_NULL
                    $stmt->bindValue(':'.$key, $val, is_numeric($val)?PDO::PARAM_INT:(is_string($val)?PDO::PARAM_STR:
                        (is_bool($val)?PDO::PARAM_BOOL:(is_null($val)?PDO::PARAM_NULL:PDO::PARAM_STR))));
                }
            }
            $stmt->execute();
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            unset($where, $fields, $field_value, $table_name, $connection, $sql, $arr, $stmt);
            return $res;
        } catch (PDOException $e) {
            unset($where, $fields, $field_value, $table_name, $connection, $sql, $arr);
            //写文件日志
            write_applog('ERROR', $e->getMessage());
            throw new Exception($e->getMessage(), CODE_DB_ERR);
        }
    }

    /**
     * @name 往数据表中插入一条数据
     * @desc
     * @param array $data 需要插入的数据,对应所有的字段
     * @return integer 如果表中有id字段,则返回之，没有就返回0，发生错误抛出异常
     */
    function insert_table($data)
    {
        if (!empty($GLOBALS['config']['split_table']) && !empty($this->_split_method) && !isset($data[$this->_split_by_field])){
            throw new Exception('缺少分表用的字段值:'.$this->_split_by_field, CODE_ERROR_IN_MODEL);
        }
        $field_value = null;
        if ( !empty($GLOBALS['config']['split_table']) && !empty($this->_split_method)){
            $field_value = $data[$this->_split_by_field];
        }

        $tables = [
            [
                'table_name' => $this->_table,
                'connection' => $this->_connection,
            ]
        ];
        $table_name = $this->sub_table($field_value);
        $connection = $this->sub_connection($field_value);
        if($table_name != $this->_table){
            $tables[] = [
                'table_name' => $table_name,
                'connection' => $connection,
            ];
        }
        unset($table_name, $connection, $field_value);
        $keys = array_keys($data);
        foreach($tables as $item) {
            $sql = 'INSERT INTO ' . $item['table_name'] . ' (`' . implode('`, `', $keys) . '`) VALUES (:' . implode(', :', $keys) . ')';
            try {
                $stmt = $GLOBALS['app']->mysql($item['connection'])->prepare($sql);
                foreach ($data as $key => $value) {
                    $stmt->bindValue(':' . $key, $value);
                }
                if (!$stmt->execute()) {
                    unset($data, $tables, $keys, $sql);
                    throw new Exception('DATABASE ERROR', CODE_DB_ERR);
                }
                unset($stmt);
            } catch (PDOException $e) {
                unset($data, $tables, $keys, $sql);
                //写文件日志
                write_applog('ERROR', $e->getMessage());
                throw new Exception($e->getMessage(), CODE_DB_ERR);
            }
            //若表中无自增的主键字段，则返回0
            $res = $GLOBALS['app']->mysql($item['connection'])->lastInsertId();
        }
        unset($data, $tables, $keys, $sql);
        return $res;
    }

    /**
     * @name 更新数据表中的一条数据
     * @desc
     * @param array $where 更新条件
     * @param array $data 需要修改的数据
     * @param string $extend_sql 延伸的SQL语句，主要用于补充where条件
     * @param array $extend_params 延伸的SQL参数及其值，主要用于给延伸的SQL语句赋值参数值
     * @return boolean
     */
    function update_table(&$where, &$data, string $extend_sql='', array $extend_params=[])
    {
        if (!empty($GLOBALS['config']['split_table']) && !empty($this->_split_method) && (!isset($where[$this->_split_by_field]) && !isset($data[$this->_split_by_field]))){
            throw new Exception('缺少分表用的字段值:'.$this->_split_by_field, CODE_ERROR_IN_MODEL);
        }
        $field_value = null;
        if ( !empty($GLOBALS['config']['split_table']) && !empty($this->_split_method) ){
            $field_value = isset($where[$this->_split_by_field]) ? $where[$this->_split_by_field] : $data[$this->_split_by_field];
        }

        $tables = [
            [
                'table_name' => $this->_table,
                'connection' => $this->_connection,
            ]
        ];
        $table_name = $this->sub_table($field_value);
        $connection = $this->sub_connection($field_value);
        if($table_name != $this->_table){
            $tables[] = [
                'table_name' => $table_name,
                'connection' => $connection,
            ];
        }
        unset($table_name, $connection, $field_value);

        $set = [];
        foreach($data as $key => $value){
            $set[] = '`'.$key.'`=:'.$key;
        }
        $where_sql = [];
        foreach ($where as $key => $value) {
            if(is_array($value)){
                if(is_array($value['value'])){
                    $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                    $where_sql[] = ' `'.$key.'` '.$value['symbol'].' ('.$plist.') ';
                }
                else{
                    $where_sql[] = ' `'.$key.'` '.$value['symbol'].' :'.$key;
                }
            }
            else{
                $where_sql[] = ' `'.$key.'`=:'.$key;
            }
        }
        $where = array_merge($where, $extend_params);
        foreach($tables as $item) {
            $sql = 'UPDATE ' . $item['table_name'] . ' SET ' . implode(',', $set) . ' WHERE ' . implode(' AND ', $where_sql) . $extend_sql;
            try {
                $stmt = $GLOBALS['app']->mysql($item['connection'])->prepare($sql);
                foreach ($data as $key => $value) {
                    $stmt->bindValue(':' . $key, $value);
                }
                foreach ($where as $key => $value) {
                    $direct_assign = true;
                    if(is_array($value)){
                        if(is_array($value['value'])){
                            $direct_assign = false;
                            $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                            $params = array_combine(explode(",", $plist), $value['value']);
                            foreach($params as $key2 => $param){
                                $stmt->bindValue($key2, $param, is_numeric($param)?PDO::PARAM_INT:(is_string($param)?PDO::PARAM_STR:
                                    (is_bool($param)?PDO::PARAM_BOOL:(is_null($param)?PDO::PARAM_NULL:PDO::PARAM_STR))));
                            }
                        }
                        else{
                            $val = $value['value'];
                        }
                    }
                    else{
                        $val = $value;
                    }
                    if($direct_assign) {
                        $stmt->bindValue(':' . $key, $val);
                    }
                }
                $stmt->execute();
                unset($stmt);
            } catch (PDOException $e) {
                unset($tables, $set, $where_sql, $sql);
                //写文件日志
                write_applog('ERROR', $e->getMessage());
                throw new Exception($e->getMessage(), CODE_DB_ERR);
            }
        }
        unset($tables, $set, $where_sql, $sql);
        return true;
    }

    /**
     * @name 删除数据表中的一条数据
     * @desc
     * @param array $where 删除条件
     * @param array $data 传分表用的字段及其值过来，如果没有分表则可不传此参数
     * @param string $extend_sql 延伸的SQL语句，主要用于补充where条件
     * @param array $extend_params 延伸的SQL参数及其值，主要用于给延伸的SQL语句赋值参数值
     * @return integer 返回删除的数量
     */
    function delete($where, $data=[], string $extend_sql='', array $extend_params=[])
    {
        if (!empty($GLOBALS['config']['split_table']) && !empty($this->_split_method) && (!isset($where[$this->_split_by_field]) && !isset($data[$this->_split_by_field]))){
            throw new Exception('缺少分表用的字段值:'.$this->_split_by_field, CODE_ERROR_IN_MODEL);
        }
        $field_value = null;
        if ( !empty($GLOBALS['config']['split_table']) && !empty($this->_split_method) ){
            $field_value = isset($where[$this->_split_by_field]) ? $where[$this->_split_by_field] : $data[$this->_split_by_field];
        }

        $tables = [
            [
                'table_name' => $this->_table,
                'connection' => $this->_connection,
            ]
        ];
        $table_name = $this->sub_table($field_value);
        $connection = $this->sub_connection($field_value);
        if($table_name != $this->_table){
            $tables[] = [
                'table_name' => $table_name,
                'connection' => $connection,
            ];
        }
        unset($table_name, $connection, $field_value);

        $where_sql = [];
        foreach ($where as $key => $value) {
            if(is_array($value)){
                if(is_array($value['value'])){
                    $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                    $where_sql[] = ' `'.$key.'` '.$value['symbol'].' ('.$plist.') ';
                }
                else{
                    $where_sql[] = ' `'.$key.'` '.$value['symbol'].' :'.$key;
                }
            }
            else{
                $where_sql[] = ' `'.$key.'`=:'.$key;
            }
        }
        $sql = '';
        if (!empty($where)){
            $sql .= ' WHERE ' . implode(' AND ', $where_sql);
        }
        $sql .= $extend_sql. ' LIMIT 1';
        $where = array_merge($where, $extend_params);
        $count = 0;
        foreach($tables as $item) {
            try {
                $stmt = $GLOBALS['app']->mysql($item['connection'])->prepare('DELETE FROM ' . $item['table_name'].$sql);
                foreach ($where as $key => $value) {
                    $direct_assign = true;
                    if(is_array($value)){
                        if(is_array($value['value'])){
                            $direct_assign = false;
                            $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                            $params = array_combine(explode(",", $plist), $value['value']);
                            foreach($params as $key2 => $param){
                                $stmt->bindValue($key2, $param, is_numeric($param)?PDO::PARAM_INT:(is_string($param)?PDO::PARAM_STR:
                                    (is_bool($param)?PDO::PARAM_BOOL:(is_null($param)?PDO::PARAM_NULL:PDO::PARAM_STR))));
                            }
                        }
                        else{
                            $val = $value['value'];
                        }
                    }
                    else{
                        $val = $value;
                    }
                    if($direct_assign) {
                        $stmt->bindValue(':' . $key, $val);
                    }
                }
                $stmt->execute();
                $count = $stmt->rowCount();
                unset($stmt);
            } catch (PDOException $e) {
                unset($tables, $set, $where_sql, $sql);
                //写文件日志
                write_applog('ERROR', $e->getMessage());
                throw new Exception($e->getMessage(), CODE_DB_ERR);
            }
        }
        unset($tables, $where_sql, $sql);
        return $count;
    }

    /**
     * @name 删除数据表中的数据（不限条数）
     * @desc
     * @param array $where 删除条件
     * @param array $data 传分表用的字段及其值过来，如果没有分表则可不传此参数
     * @param string $extend_sql 延伸的SQL语句，主要用于补充where条件
     * @param array $extend_params 延伸的SQL参数及其值，主要用于给延伸的SQL语句赋值参数值
     * @return integer 返回删除的数量
     */
    function destroy($where, $data=[], string $extend_sql='', array $extend_params=[])
    {
        if (!empty($GLOBALS['config']['split_table']) && !empty($this->_split_method) && (!isset($where[$this->_split_by_field]) && !isset($data[$this->_split_by_field]))){
            throw new Exception('缺少分表用的字段值:'.$this->_split_by_field, CODE_ERROR_IN_MODEL);
        }
        $field_value = null;
        if ( !empty($GLOBALS['config']['split_table']) && !empty($this->_split_method) ){
            $field_value = isset($where[$this->_split_by_field]) ? $where[$this->_split_by_field] : $data[$this->_split_by_field];
        }

        $tables = [
            [
                'table_name' => $this->_table,
                'connection' => $this->_connection,
            ]
        ];
        $table_name = $this->sub_table($field_value);
        $connection = $this->sub_connection($field_value);
        if($table_name != $this->_table){
            $tables[] = [
                'table_name' => $table_name,
                'connection' => $connection,
            ];
        }
        unset($table_name, $connection, $field_value);

        $where_sql = [];
        foreach ($where as $key => $value) {
            if(is_array($value)){
                if(is_array($value['value'])){
                    $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                    $where_sql[] = ' `'.$key.'` '.$value['symbol'].' ('.$plist.') ';
                }
                else{
                    $where_sql[] = ' `'.$key.'` '.$value['symbol'].' :'.$key;
                }
            }
            else{
                $where_sql[] = ' `'.$key.'`=:'.$key;
            }
        }
        $sql = '';
        if (!empty($where)){
            $sql .= ' WHERE ' . implode(' AND ', $where_sql);
        }
        $sql .= $extend_sql;
        $where = array_merge($where, $extend_params);
        $count = 0;
        foreach($tables as $item) {
            try {
                $stmt = $GLOBALS['app']->mysql($item['connection'])->prepare('DELETE FROM ' . $item['table_name'].$sql);
                foreach ($where as $key => $value) {
                    $direct_assign = true;
                    if(is_array($value)){
                        if(is_array($value['value'])){
                            $direct_assign = false;
                            $plist = ':'.$key.'_'.implode(',:'.$key.'_', array_keys($value['value']));
                            $params = array_combine(explode(",", $plist), $value['value']);
                            foreach($params as $key2 => $param){
                                $stmt->bindValue($key2, $param, is_numeric($param)?PDO::PARAM_INT:(is_string($param)?PDO::PARAM_STR:
                                    (is_bool($param)?PDO::PARAM_BOOL:(is_null($param)?PDO::PARAM_NULL:PDO::PARAM_STR))));
                            }
                        }
                        else{
                            $val = $value['value'];
                        }
                    }
                    else{
                        $val = $value;
                    }
                    if($direct_assign) {
                        $stmt->bindValue(':' . $key, $val);
                    }
                }
                $stmt->execute();
                $count = $stmt->rowCount();
                unset($stmt);
            } catch (PDOException $e) {
                unset($tables, $set, $where_sql, $sql);
                //写文件日志
                write_applog('ERROR', $e->getMessage());
                throw new Exception($e->getMessage(), CODE_DB_ERR);
            }
        }
        unset($tables, $where_sql, $sql);
        return $count;
    }

    /**
     * @name 更新相关统计数量的字段
     * @desc 即可以加减数量的字段
     * @param array $where 更新条件
     * @param array $fields 需要更新的字段及其增加或减少的数量，增加用正数，减少用负数
     * @param string $extend_sql 延伸的SQL语句，主要用于补充where条件
     * @param array $extend_params 延伸的SQL参数及其值，主要用于给延伸的SQL语句赋值参数值
     * @return boolean
     */
    public function update_count_field(array $where, array $fields, string $extend_sql='', array $extend_params=[]){
        if (!$where || !$fields){
            return true;
        }
        $arr = [];
        foreach ($fields as $key => $value){
            $value = intval($value);
            if ($value<0){
                $arr[] = '`'.$key.'`=`'.$key.'`'.$value;
            }
            else if ($value>0){
                $arr[] = '`'.$key.'`=`'.$key.'`+'.$value;
            }
        }
        if (!$arr){
            return true;
        }
        $where_arr = [];
        foreach ($where as $key => $value){
            $where_arr[] = '`'.$key.'`=:'.$key;
        }

        $sql = 'UPDATE '.$this->get_table().' SET '.implode(',', $arr).' WHERE '.implode(' AND ', $where_arr). $extend_sql;
        $connection = $this->sub_connection();
        $stmt = $GLOBALS['app']->mysql($connection)->prepare($sql);
        $where = array_merge($where, $extend_params);
        foreach ($where as $key => $value){
            $stmt->bindValue(':'.$key, $value, is_numeric($value)?PDO::PARAM_INT:(is_string($value)?PDO::PARAM_STR:
                (is_bool($value)?PDO::PARAM_BOOL:(is_null($value)?PDO::PARAM_NULL:PDO::PARAM_STR))));
        }
        $stmt->execute();
        unset($uid, $arr, $fields, $sql, $stmt, $connection, $key, $value, $where, $where_arr);
        return true;
    }

    /**
     * @name 更新有频率限制的统计字段数量
     * @desc 如点赞数、浏览数
     * @param string $vk_redis_key 针对vk的缓存键
     * @param string $ip_redis_key 针对ip的缓存键
     * @param integer $ip_short_count_limit 针对 ip 5秒内的最大允许增加数量
     * @param integer $ip_hour_count_limit 针对 ip 1小时内的最大允许增加数量
     * @param array $where 更新数据库的条件
     * @param array $data 更新数据库的增加值，即在原来的基础上需要再增加的数量
     * @param string $extend_sql 延伸的SQL语句，主要用于补充where条件
     * @param array $extend_params 延伸的SQL参数及其值，主要用于给延伸的SQL语句赋值参数值
     * @return boolean
     * @throws
     */
    public function add_limit_field_count($vk_redis_key, $ip_redis_key, $ip_short_count_limit,
                                          $ip_hour_count_limit, $where, $data, string $extend_sql='', array $extend_params=[]){
        global $app;
        $max_time = round(microtime(true)*10000);
        //增加该ip的浏览记录
        $app->redis()->rpush($ip_redis_key, $max_time);
        //修剪掉超过3万的老数据
        $app->redis()->ltrim($ip_redis_key, -$ip_hour_count_limit, -1);
        //续期1小时
        $app->redis()->EXPIRE($ip_redis_key, TIME_HOUR);

        //检查该ip在5秒内的浏览频率是否超过1千
        $value = $app->redis()->lindex($ip_redis_key, $ip_short_count_limit);
        if (!empty($value) && $value>=$max_time-50000){
            return false;
        }
        //检查该ip在1小时内的浏览频率是否超过3万
        $value = $app->redis()->lindex($ip_redis_key, 0);
        if ($app->redis()->llen($ip_redis_key)>=$ip_hour_count_limit && $value>=$max_time-(TIME_HOUR*10000)){
            return false;
        }
        //检查该vk在10分钟内是否增加过浏览次数
        if ($app->redis()->exists($vk_redis_key)){
            return false;
        }
        //增加浏览次数
        $this->update_count_field($where, $data, $extend_sql, $extend_params);

        //更新该vk最新增加浏览次数的时间
        $app->redis()->set($vk_redis_key, 1);
        $app->redis()->EXPIRE($vk_redis_key, TIME_15_MIN);
        return true;
    }
}
