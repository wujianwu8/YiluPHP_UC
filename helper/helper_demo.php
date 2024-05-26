<?php

/**
 * 样例文件，可删除
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * Date: 2021.01.01
 * Time: 11:19
 */
class helper_demo extends base_class
{
    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    /**
     * @name 测试调用helper类
     * @desc
     * @return string
     * @throws
     */
    public function test_helper()
    {
        return model_demo::I()->test_for_helper();
    }
}
