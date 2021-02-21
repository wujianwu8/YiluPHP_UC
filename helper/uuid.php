<?php
/*
 * UUID生成类，本类使用Redis实现唯一ID的生成，分类递增的方式生成唯一ID
 * UUID 是 通用唯一识别码（Universally Unique Identifier）的缩写，是一种软件建构的标准，亦为开放软件基金会组织在分布式计算环境领域的一部分。
 * 其目的，是让分布式系统中的所有元素，都能有唯一的辨识信息，而不需要通过中央控制端来做辨识信息的指定。如此一来，每个人都可以创建不与其它人冲突的UUID。
 * 在这样的情况下，就不需考虑数据库创建时的名称重复问题。
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 19:22
 */

class uuid extends base_class
{
    /**
     * @name 生成一个新的用户ID
     * @desc 生成一个新的用户ID
     * @return integer
     */
	public function newUserId()
	{
	    return $this->_createNewId('user_id');
	}

    /**
     * @name 根据指定的类型生成一个该类型中唯一的ID
     * @desc 根据指定的类型生成一个该类型中唯一的ID
     * @param string $type
     * @return integer
     */
	private function _createNewId($type)
	{
	    //REDIS的第10个库用户存储持久固定的数据，不能被清空
	    return redis_y::I('default',10)->hincrby('YILUUC_UUID', $type, 1);
	}
}
