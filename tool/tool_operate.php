<?php
/*
 * 方便内容运营的工具类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/08/12
 * Time: 20:58
 */


class tool_operate
{
    //存储单例
    private static $_instance = null;

    /**
     * 获取单例
     * @return model|null 返回单例
     */
    public static function I(){
        if (!static::$_instance){
            return static::$_instance = new self();
        }
        return static::$_instance;
    }

    /**
     * 通过企业微信的群机器人发送工作通知
     * 接口文档 https://work.weixin.qq.com/api/doc/90000/90136/91770
     * @param string $content 文本内容，最长不超过2048个字节，必须是utf8编码
     * @param string $robot_key 机器人编码，不同的机器人的编码不一样，可按业务创建不同的机器人
     * @param string $msgtype  消息类型 默认是文本，支持文本（text）、markdown（markdown）、图片（image）、图文（news）四种消息类型。文本不可发链接，可用markdown
     * @param array $mentioned_list userid的列表，提醒群中的指定成员(@某个成员)，@all表示提醒所有人，如果开发者获取不到userid，可以使用mentioned_mobile_list
     * @param array $mentioned_mobile_list 手机号列表，提醒手机号对应的群成员(@某个成员)，@all表示提醒所有人
     * @return array
     * @throws fx_da_exception
     */
    public function send_work_notice($content, $robot_key, $msgtype='text', $mentioned_list=[], $mentioned_mobile_list=[])
    {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=" . $robot_key;
        $post_data = [
            'msgtype' => $msgtype,
            $msgtype => [
                'content' => $content,
                'mentioned_list' => $mentioned_list,
                'mentioned_mobile_list' => $mentioned_mobile_list,
            ]
        ];
        return curl::I()->postJson($url, json_encode($post_data));
    }

}
