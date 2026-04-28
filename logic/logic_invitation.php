<?php
/**
 * 邀请逻辑处理类
 */
class logic_invitation extends base_class
{
    const SCENE_REGISTER = 'register';

    /**
     * @name 校验邀请场景
     * @desc
     * @param string $scene 场景
     * @return boolean
     */
    public function is_valid_scene(string $scene)
    {
        return preg_match('/^[a-z][a-z0-9_]{0,31}$/', $scene) === 1;
    }

    /**
     * @name 生成邀请码
     * @desc 从6位字符开始生成，若冲突则自动增加长度
     * @param integer $start_length 起始长度
     * @return string
     * @throws
     */
    public function generate_invite_code(int $start_length = 6)
    {
        $length = $start_length < 6 ? 6 : $start_length;
        for ($round = 0; $round < 10; $round++) {
            for ($i = 0; $i < 20; $i++) {
                $invite_code = rand_string($length);
                if (!model_invitation_link::I()->find_by_invite_code($invite_code)) {
                    return $invite_code;
                }
            }
            $length++;
        }
        throw new Exception('生成邀请码失败', CODE_FAIL_TO_GENERATE_UID);
    }

    /**
     * @name 获取默认cookie有效期
     * @desc
     * @return integer
     */
    public function get_default_cookie_ttl()
    {
        return TIME_DAY * 15;
    }

    /**
     * @name 根据邀请码读取邀请链接
     * @desc
     * @param string $invite_code 邀请码
     * @return array|null
     */
    public function find_by_invite_code(string $invite_code)
    {
        if ($invite_code === '') {
            return null;
        }
        $link = model_invitation_link::I()->find_by_invite_code($invite_code);
        if (!$link) {
            return null;
        }
        if (empty($link['cookie_ttl']) || intval($link['cookie_ttl']) < 1) {
            $link['cookie_ttl'] = $this->get_default_cookie_ttl();
        }
        return $link;
    }

    /**
     * @name 根据用户ID和场景读取邀请链接
     * @desc
     * @param integer $uid 用户ID
     * @param string $scene 场景
     * @return array|null
     */
    public function find_by_uid_and_scene(int $uid, string $scene)
    {
        if ($uid < 1 || !$this->is_valid_scene($scene)) {
            return null;
        }
        $link = model_invitation_link::I()->find_by_uid_and_scene($uid, $scene);
        if (!$link) {
            return null;
        }
        if (empty($link['cookie_ttl']) || intval($link['cookie_ttl']) < 1) {
            $link['cookie_ttl'] = $this->get_default_cookie_ttl();
        }
        return $link;
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
        if ($uid < 1) {
            return [];
        }
        if ($scene !== '' && !$this->is_valid_scene($scene)) {
            return [];
        }
        $list = model_invitation_link::I()->select_by_uid($uid, $scene);
        foreach ($list as $key => $item) {
            if (empty($item['cookie_ttl']) || intval($item['cookie_ttl']) < 1) {
                $list[$key]['cookie_ttl'] = $this->get_default_cookie_ttl();
            }
        }
        return $list;
    }

    /**
     * @name 创建或补齐邀请链接
     * @desc
     * @param integer $uid 用户ID
     * @param string $scene 场景
     * @param string $remark 备注
     * @param integer $cookie_ttl cookie有效期，秒
     * @return array|null
     * @throws
     */
    public function create_invitation_link(int $uid, string $scene, string $remark = '', int $cookie_ttl = 0)
    {
        if ($uid < 1 || !$this->is_valid_scene($scene)) {
            return null;
        }
        $cookie_ttl = $cookie_ttl > 0 ? $cookie_ttl : $this->get_default_cookie_ttl();
        $time = time();
        if ($link = model_invitation_link::I()->find_by_uid_and_scene($uid, $scene)) {
            $data = [
                'cookie_ttl' => $cookie_ttl,
                'mtime' => $time,
            ];
            if ($remark !== '') {
                $data['remark'] = $remark;
            }
            model_invitation_link::I()->update_table(['id' => $link['id']], $data);
            return $this->find_by_uid_and_scene($uid, $scene);
        }
        $data = [
            'uid' => $uid,
            'scene' => $scene,
            'invite_code' => $this->generate_invite_code(),
            'cookie_ttl' => $cookie_ttl,
            'remark' => $remark,
            'mtime' => $time,
            'ctime' => $time,
        ];
        $id = model_invitation_link::I()->insert_table($data);
        return model_invitation_link::I()->find_table(['id' => $id]);
    }

    /**
     * @name 更新邀请链接
     * @desc
     * @param integer $id 邀请链接ID
     * @param array $data 需要更新的数据
     * @return array|null
     */
    public function update_invitation_link(int $id, array $data)
    {
        if ($id < 1) {
            return null;
        }
        $data['mtime'] = time();
        model_invitation_link::I()->update_table(['id' => $id], $data);
        return model_invitation_link::I()->find_table(['id' => $id]);
    }

    /**
     * @name 更换邀请码
     * @desc
     * @param integer $id 邀请链接ID
     * @return array|null
     * @throws
     */
    public function refresh_invite_code(int $id)
    {
        if ($id < 1 || !$link = model_invitation_link::I()->find_table(['id' => $id])) {
            return null;
        }
        $data = [
            'invite_code' => $this->generate_invite_code(),
            'mtime' => time(),
        ];
        model_invitation_link::I()->update_table(['id' => $id], $data);
        return model_invitation_link::I()->find_table(['id' => $id]);
    }

    /**
     * @name 生成注册链接
     * @desc
     * @param string $invite_code 邀请码
     * @return string
     */
    public function build_register_url(string $invite_code)
    {
        $scheme = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        return $scheme . $host . url_pre_lang() . '/sign/up?invite_code=' . urlencode($invite_code);
    }

    /**
     * @name 从邀请码生成cookie键名
     * @desc
     * @param string $scene 场景
     * @return string
     */
    public function get_cookie_name(string $scene)
    {
        return 'invite_' . $scene;
    }
}
