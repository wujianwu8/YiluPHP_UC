<?php
/**
 * 模型类，样例文件，可删除
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * Date: 2021.01.01
 * Time: 11:19
 */

class model_demo extends model
{
    //数据表名称
    protected $_table = 'table_name_demo';

    /**
     * @name 给controller调用
     * @desc
     * @return string
     * @throws
     */
    public function find_test()
    {
        return 'Data from model_demo.php';
    }

    /**
     * @name 给helper调用
     * @desc
     * @return string
     * @throws
     */
    public function test_for_helper()
    {
        return 'Data from model_demo.php for helper_demo.php';
    }
}
