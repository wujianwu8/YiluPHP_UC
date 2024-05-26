<?php
/**
 * UUID表模型类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * Date: 2022/03/18
 * Time: 21:50
 */

class model_uuid_stock extends model
{
    protected $_table = 'uuid_stock';

    /**
     * 添加一批UUID
     * @param $count 需要添加的UUID数量，如果大于500万，则会被强制设置为500万
     * @return array
     * @throws Exception
     */
    public function batch_insert_uuid($count)
    {
        $count = intval($count);
        if ($count < 0) {
            $count = 100000;
        }
        if ($count > 5000000) {
            $count = 5000000;
        }
        $sql = <<<sql
CALL batchInsertUUID({$count});
sql;
        return $this->execute_sql($sql);
    }
}
