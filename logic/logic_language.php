<?php
/**
 * 语言包逻辑处理类
 * YiluPHP vision 2.1
 * User: Jim.Wu
 * Date: 2025/05/12
 * Time: 21:41
 **/

class logic_language extends base_class
{
    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    /**
     * 把语言包内容写入PHP文件
     * @param $project_info
     * @param $nickname
     * @param $uid
     * @return void
     * @throws validate_exception
     */
    public function write_to_php_file($project_info, $nickname = '', $uid = 0)
    {
        if (empty($project_info['file_dir'])) {
            unset($project_info);
            throw new validate_exception('PHP语言包目录设置不正确', 4);
        }

        //读取语言包文件
        if (!is_dir($project_info['file_dir'])) {
            unset($project_info);
            throw new validate_exception('PHP语言包目录不存在', 5);
        }

        $project_info['language_types'] = explode(',', $project_info['language_types']);
        $file_list = get_dir_and_file($project_info['file_dir'], 'file');
        if (substr($project_info['file_dir'], -1) != '/' && substr($project_info['file_dir'], -1) != '\\') {
            $separator = DIRECTORY_SEPARATOR;
        }
        else {
            $separator = '';
        }

        $project_info['file_dir'] .= $separator;
        unset($separator);

        $if_have_php_file = false;
        foreach ($file_list as $file) {
            $file_info = pathinfo($file);
            if (strtolower($file_info['extension']) == 'php') {
                $if_have_php_file = true;
                break;
            }
        }

        if ($if_have_php_file) {
            //备份原来的语言包文件
            $zip = new ZipArchive();
            $zip->open($project_info['file_dir'] . 'bak-' . date('Ymd-His') . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
            foreach ($file_list as $file) {
                $file_info = pathinfo($file);
                if (strtolower($file_info['extension']) != 'php') {
                    continue;
                }
                $zip->addFile($project_info['file_dir'] . $file, $file);
            }
            $zip->close();
            unset($zip);
        }

        foreach ($project_info['language_types'] as $lang) {
            if (!$langfile = fopen($project_info['file_dir'] . $lang . '.php', "w")) {
                throw new validate_exception('打开文件失败：' . $project_info['file_dir'] . $lang . '.php', 10);
            }
            $txt = "<?php
/**
 * Created by UserCenter System
 * User: " . $nickname . "
 * UID: " . $uid . "
 * Date: " . date('Y/m/d') . "
 * Time: " . date('H:i') . "
 */
return [\r\n";
            fwrite($langfile, $txt);
            //读取该项目、该语言的所有语言键及内容，按语言键字母升序排序
            if ($lang_list = model_language_value::I()->select_all([
                'project_key'   => $project_info['project_key'],
                'language_type' => $lang,
                'output_type'   => [
                    'symbol' => 'LIKE',
                    'value'  => '%-PHP-%'
                ]
            ], 'language_key ASC', 'language_key,language_value')) {
                foreach ($lang_list as $item) {
                    $item['language_value'] = stripslashes($item['language_value']);
                    $txt = "    '" . $item['language_key'] . "' => '" . addslashes($item['language_value']) . "',\r\n";
                    //写入文件
                    fwrite($langfile, $txt);
                }
            }
            fwrite($langfile, "];\r\n");
            fclose($langfile);
        }
        unset($project_info, $lang);
    }

    /**
     * 把语言包内容写入JS文件
     * @param $project_info
     * @return void
     * @throws validate_exception
     */
    public function write_to_js_file($project_info)
    {
        if (empty($project_info['js_file_dir'])) {
            unset($project_info);
            throw new validate_exception('JS语言包目录设置不正确', 4);
        }

        //读取语言包文件
        if (!is_dir($project_info['js_file_dir'])) {
            unset($project_info);
            throw new validate_exception('JS语言包目录不存在', 5);
        }

        $project_info['language_types'] = explode(',', $project_info['language_types']);
        $file_list = get_dir_and_file($project_info['js_file_dir'], 'file');
        if (substr($project_info['js_file_dir'], -1) != '/' && substr($project_info['js_file_dir'], -1) != '\\') {
            $separator = DIRECTORY_SEPARATOR;
        }
        else {
            $separator = '';
        }

        $project_info['js_file_dir'] .= $separator;
        unset($separator);

        $if_have_js_file = false;
        foreach ($file_list as $file) {
            $file_info = pathinfo($file);
            if (strtolower($file_info['extension']) == 'js') {
                $if_have_js_file = true;
                break;
            }
        }

        if ($if_have_js_file) {
            //备份原来的语言包文件
            $zip = new ZipArchive();
            $zip->open($project_info['js_file_dir'] . 'bak-' . date('Ymd-His') . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
            foreach ($file_list as $file) {
                $file_info = pathinfo($file);
                if (strtolower($file_info['extension']) != 'js') {
                    continue;
                }
                $zip->addFile($project_info['js_file_dir'] . $file, $file);
            }
            $zip->close();
            unset($zip);
        }

        foreach ($project_info['language_types'] as $lang) {
            if (!$langfile = fopen($project_info['js_file_dir'] . $lang . '.js', "w")) {
                throw new validate_exception('打开文件失败：' . $project_info['js_file_dir'] . $lang . '.js', 10);
            }
            $txt = "var language = {\r\n";
            fwrite($langfile, $txt);
            //读取该项目、该语言的所有语言键及内容，按语言键字母升序排序
            if ($lang_list = model_language_value::I()->select_all([
                'project_key'   => $project_info['project_key'],
                'language_type' => $lang,
                'output_type'   => [
                    'symbol' => 'LIKE',
                    'value'  => '%-JS-%'
                ]
            ], 'language_key ASC', 'language_key,language_value')) {
                foreach ($lang_list as $key => $item) {
                    $txt = "    " . $item['language_key'] . ": \"" . addslashes($item['language_value']) . "\"";
                    if ($key < count($lang_list) - 1) {
                        $txt .= ",\r\n";
                    }
                    else {
                        $txt .= "\r\n";
                    }
                    //写入文件
                    fwrite($langfile, $txt);
                }
            }
            fwrite($langfile, "};\r\n");
            fclose($langfile);
        }
        unset($project_info, $lang);
    }

}
