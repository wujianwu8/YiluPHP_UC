<?php
/**
 * 上传文件记录模型类
 * 对应数据表 `file`，用于记录系统中所有用户上传的文件，便于后续按 type 检查文件是否仍被引用、清理无引用的文件。
 */
class model_file extends model
{
    protected $_table = 'file';

    /**
     * @name 保存一条上传文件记录
     * @desc 入库失败仅写日志，不抛异常，不影响调用方主流程（文件已经上传成功的情况下，不应因为日志/记录失败而让接口返回失败）
     * @param string $url  文件访问地址（开启 OSS 时为 OSS 完整 URL，否则为本地静态路径）
     * @param string $type 文件用途类型，对应 file 表 type 字段，由各子系统自定义，如 avatar，默认空字符串
     * @param int    $uid  上传者用户ID，未登录时传 0
     * @param string $ip   上传者IP
     * @return int|false   成功返回 file_id，失败返回 false
     */
    public function insert_file(string $url, string $type = '', int $uid = 0, string $ip = '')
    {
        try {
            return $this->insert_table([
                'url'       => $url,
                'type'      => $type,
                'create_at' => time(),
                'ip'        => $ip,
                'uid'       => $uid,
            ]);
        }
        catch (Exception $e) {
            write_applog('ERROR', '保存上传文件记录失败：' . $e->getMessage()
                . ' url=' . $url . ' type=' . $type . ' uid=' . $uid . ' ip=' . $ip);
            return false;
        }
    }
}
