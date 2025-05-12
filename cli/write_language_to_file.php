<?php
/**
 * 将语言翻译写入语言包文件
 * 运行方式如：/usr/local/php7/bin/php /data/web/passport.thegundcompany.cn/yilu write_language_to_file
 * 这个命令中/usr/local/php7/bin/php是你的PHP安装位置
 * 这是CLI命令的入口：yilu
 * YiluPHP vision 2.1
 * User: Jim.Wu
 * Date: 2025.05.12
 * Time: 22:09
 **/

//获取所有的语言包项目
if (!$project_list = model_language_project::I()->select_all([])) {
    unset($project_info);
    exit("\r\n完成，没有项目的语言包需要更新');\r\n\r\n");
}

$last_modify_times = redis_y::I()->hGetAll(REDIS_KEY_HASH_LAST_WRITE_LANG_FILE);
$nickname = 'CLI脚本文件（CLI Script File）：write_language_to_file.php';
$time = time();
foreach ($project_list as $project_info) {
    if (substr($project_info['file_dir'], -1) != '/' && substr($project_info['file_dir'], -1) != '\\') {
        $separator = DIRECTORY_SEPARATOR;
    }
    else {
        $separator = '';
    }

    $langs = explode(',', $project_info['language_types']);
    $lang_file = $project_info['file_dir'] . $separator . $langs[0] . '.php';
    if (!empty($project_info['file_dir']) && is_dir($project_info['file_dir'])) {
        $if_write = true;
        $index = 'php:' . $project_info['project_key'];
        if (!empty($last_modify_times[$index]) && is_file($lang_file)) {
            //获取该文件的最后修改时间
            $last_modify_time = filemtime($lang_file);
            if ($last_modify_times[$index] <= $last_modify_time) {
                $if_write = false;
            }
        }

        if ($if_write) {
            try {
                logic_language::I()->write_to_php_file($project_info, $nickname, 0);
                echo '已将 ' . $project_info['project_name'] . ' 的语言包写入 PHP 文件' . "\r\n";
                if (empty($last_modify_times[$index])) {
                    $last_modify_times[$index] = $time;
                    redis_y::I()->hSet(REDIS_KEY_HASH_LAST_WRITE_LANG_FILE, $index, $time);
                }
            }
            catch (Exception $e) {
                echo $project_info['project_name'] . '，code ' . $e->getCode() . ' : ' . $e->getMessage() . "\r\n";
            }
        }
    }


    $lang_file = $project_info['js_file_dir'] . '/' . $langs[0] . '.js';
    if (!empty($project_info['js_file_dir']) && is_dir($project_info['js_file_dir'])) {
        $if_write = true;
        $index = 'js:' . $project_info['project_key'];
        if (!empty($last_modify_times[$index]) && is_file($lang_file)) {
            //获取该文件的最后修改时间
            $last_modify_time = filemtime($lang_file);
            if ($last_modify_times[$index] <= $last_modify_time) {
                $if_write = false;
            }
        }

        if ($if_write) {
            try {
                logic_language::I()->write_to_js_file($project_info);
                echo '已将 ' . $project_info['project_name'] . ' 的语言包写入 JS 文件' . "\r\n";
                if (empty($last_modify_times[$index])) {
                    $last_modify_times[$index] = $time;
                    redis_y::I()->hSet(REDIS_KEY_HASH_LAST_WRITE_LANG_FILE, $index, $time);
                }
            }
            catch (Exception $e) {
                echo $project_info['project_name'] . '，code ' . $e->getCode() . ' : ' . $e->getMessage() . "\r\n";
            }
        }
    }
}

if (!empty($last_modify_times)) {
    //延长过期时间
    redis_y::I()->expire(REDIS_KEY_HASH_LAST_WRITE_LANG_FILE, TIME_30_DAY);
}

exit("\r\n完成\r\n\r\n");