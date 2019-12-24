<?php
/*
 * 菜单逻辑处理类
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/09
 * Time: 21:39
 */

class logic_menus
{
	public function __construct()
	{
	}

	public function __destruct()
	{
	}

    /**
     * @name 获取所有的菜单
     * @desc 根据层次和顺序排列好
     * @param integer $uid 用户ID
     * @return array
	 * @throws
     */
    public function get_all($uid=null)
    {
		global $app;
		$app->redis()->del(REDIS_KEY_ALL_MENUS);
		if($data = $app->redis()->get(REDIS_KEY_ALL_MENUS)){
            $data = json_decode($data, true);
		}

		if (!$data) {
            $data = $app->model_menus->select_all(['parent_menu' => 0], '`position` DESC, weight ASC, ctime DESC');
            foreach ($data as $key => $item) {
                $data[$key]['children'] = $app->model_menus->select_all(['parent_menu' => $item['id']], 'weight ASC, ctime DESC');
            }
            if ($data) {
                $app->redis()->set(REDIS_KEY_ALL_MENUS, json_encode($data));
                $app->redis()->expire(REDIS_KEY_ALL_MENUS, TIME_DAY);
            }
            unset($key, $item);
        }

		if ($uid){
		    //过滤掉用户没有权限的菜单
            foreach ($data as $key => $item) {
                if (trim($item['permission'])) {
                    $tmp = explode(':', $item['permission']);
                    if (count($tmp)!=2){
                        continue;
                    }
                    if (!$app->model_user_permission->if_has_permission($uid, $tmp[1], $tmp[0])) {
                        unset($data[$key]);
                        continue;
                    }
                }
                if (!empty($item['children'])){
                    foreach ($item['children'] as $key2 => $item2) {
                        if (trim($item2['permission'])) {
                            $tmp = explode(':', $item2['permission']);
                            if (count($tmp)!=2){
                                continue;
                            }
                            if (!$app->model_user_permission->if_has_permission($uid, $tmp[1], $tmp[0])) {
                                unset($data[$key]['children'][$key2]);
                                continue;
                            }
                        }
                    }
                    if (count($data[$key]['children'])==0){
                        unset($data[$key]);
                        continue;
                    }
                }
            }
        }
        unset($app);
		return $data;
    }

}
