<?php
/**
 * 邀请码捕获前置hook
 * 把此类名加入到配置的before_controller中即可生效，建议放在hook_route_auth之前
 * 当GET参数中包含invite_code时，根据邀请码查询邀请链接，将邀请码写入对应场景的cookie
 */
class hook_invitation_capture extends hook
{
    public function run()
    {
    }

    public function __construct()
    {
        if (empty($_GET['invite_code'])) {
            return;
        }
        $invite_code = trim($_GET['invite_code']);
        if ($invite_code === '' || !preg_match('/^[A-Za-z0-9]{4,64}$/', $invite_code)) {
            return;
        }
        $link = logic_invitation::I()->find_by_invite_code($invite_code);
        if (!$link) {
            return;
        }
        $scene = $link['scene'];
        $cookie_name = logic_invitation::I()->get_cookie_name($scene);
        if (isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] !== '') {
            $old_link = logic_invitation::I()->find_by_invite_code($_COOKIE[$cookie_name]);
            if ($old_link && $old_link['scene'] === $scene) {
                return;
            }
        }
        $domain = isset($GLOBALS['config']['root_domain']) ? $GLOBALS['config']['root_domain'] : '';
        $ttl = intval($link['cookie_ttl']);
        if ($ttl < 1) {
            $ttl = logic_invitation::I()->get_default_cookie_ttl();
        }
        $_COOKIE[$cookie_name] = $invite_code;
        setcookie($cookie_name, $invite_code, time() + $ttl, '/', $domain);
    }

    public function __destruct()
    {
    }
}
