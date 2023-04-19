<?php
/**
 * @desc 根据接口注释信息自动生成接口文档
 * @method GET
 * @uri /api_docs
 * @param string path 文件路径 可选 示例用
 * @param string version 版本 可选 默认为全部
 * @return html
 * @exception
 */


if (!function_exists('add_url_params')){
    /**
     * @name 向URL中增加参数
     * @desc
     * @param string $url
     * @param array $params
     * @return string
     */
    function add_url_params ($url, $params){
        $temp = parse_url($url);
        $url_well = explode('#', $url);
        $url = explode('?', $url_well[0]);
        $url_params = [];
        if (count($url)>1){
            $temp = explode('&', $url[1]);
            foreach ($temp as $value){
                $value = explode('=', $value);
                $url_params[$value[0]] = isset($value[1])?$value[1]:'';
            }
        }
        foreach ($params as $key=>$value){
            $url_params[$key] = $value;
        }
        $temp = [];
        foreach ($url_params as $key=>$value){
            $temp[] = $key.'='.$value;
        }
        $url = $url[0].'?'.implode('&', $temp);
        if (count($url_well)>1){
            $url .= '#'.$url_well[1];
        }
        unset($temp, $url_params, $key, $value, $url_well);
        return $url;
    }
}


if (!isset($_COOKIE['vk'])){
    $domain = isset($GLOBALS['config']['root_domain']) ? $GLOBALS['config']['root_domain'] : '';
    $_COOKIE['vk'] = md5(client_ip().microtime().uniqid());
    setcookie('vk', $_COOKIE['vk'], time()+5184000, '/', $domain);
}
$cache_key = 'VISIT_API_DOCS_SIGN_'.$_COOKIE['vk'];
if (!empty($_GET['logout'])){
    redis_y::I()->del($cache_key);
}
$is_login = false;

if (!empty($config['visit_api_docs_password'])){
    if (redis_y::I()->exists($cache_key)){
        redis_y::I()->EXPIRE($cache_key, 3600);
        $is_login = true;
    }
    else{
        if (empty($_POST['passport'])) {
            showVisitApiDosLoginUI();
        }
        else{
            if ($config['visit_api_docs_password'] == $_POST['passport']){
                redis_y::I()->set($cache_key, 1);
                redis_y::I()->EXPIRE($cache_key, 3600);
                $is_login = true;
            }
            else{
                echo '<p style="text-align: center; color: #d00000;">Passport error.</p>';
                showVisitApiDosLoginUI();
            }
        }
    }
}
else if (!in_array(env(), ['dev', 'local'])){
    return code(CODE_NO_AUTHORIZED,'Can\'t visited in '.env().' environment');
}

$menu_list = [
//    'md5(组名)' => [
//        [
//            'group' => '组名',
//            'version' => ['所属版本1','所属版本2'],
//            'path' => '文件路径',
//            'name' => '接口名',
//            'desc' => '接口描述',
//            'uri' => '接口地址',
//            'return' => '返回数据类型',
//            'exception' => '异常状态列举',
//            'params' => [
//                'data_type' => '数据类型',
//                'param_key' => '参数字段名',
//                'param_name' => '参数名',
//                'required' => '是否必需？',
//                'remark' => '参数描述',
//            ],
//        ],
//        ...
//    ],
//    ...
];

$path = input::I()->get_trim('path', '');
$version = input::I()->get_trim('version', '');
$first_api = null;
$current_api = null;
$version_list = [];

$file_list1 = get_dir_and_file(APP_PATH.'controller');
foreach ($file_list1 as $key1 => $item1){
    if (is_integer($key1)){
        if ($data = parseApiAnnotation(APP_PATH.'controller/'.$item1)){
            if ($version!='' && !in_array($version, $data['version'])){
                continue;
            }
            $data['path'] = $item1;
            if (empty($first_api)){
                $first_api = $data;
            }
            if ($path!='' && $path==$data['path']){
                $current_api = $data;
            }
            $data = [
                'group' => $data['group'],
                'name' => $data['name'],
                'path' => $data['path'],
            ];
            if (empty($data['group'])){
                $menu_list[0][] = $data;
            }
            else{
                $menu_list[md5($data['group'])][] = $data;
            }
        }
    }
    else{
        $file_list2 = get_dir_and_file(APP_PATH.'controller/'.$item1);
        foreach ($file_list2 as $key2 => $item2){
            if (is_integer($key2)){
                if ($data = parseApiAnnotation(APP_PATH.'controller/'.$item1.'/'.$item2)){
                    if ($version!='' && !in_array($version, $data['version'])){
                        continue;
                    }
                    $data['path'] = $item1.'/'.$item2;
                    if (empty($first_api)){
                        $first_api = $data;
                    }
                    if ($path!='' && $path==$data['path']){
                        $current_api = $data;
                    }
                    $data = [
                        'group' => $data['group'],
                        'name' => $data['name'],
                        'path' => $data['path'],
                    ];
                    if (!$data['group']){
                        $menu_list[0][] = $data;
                    }
                    else{
                        $menu_list[md5($data['group'])][] = $data;
                    }
                }
            }
            else{
                $file_list3 = get_dir_and_file(APP_PATH.'controller/'.$item1.'/'.$item2);
                foreach ($file_list3 as $key3 => $item3){
                    if (is_integer($key3)){
                        if ($data = parseApiAnnotation(APP_PATH.'controller/'.$item1.'/'.$item2.'/'.$item3)){
                            if ($version!='' && !in_array($version, $data['version'])){
                                continue;
                            }
                            $data['path'] = $item1.'/'.$item2.'/'.$item3;
                            if (empty($first_api)){
                                $first_api = $data;
                            }
                            if ($path!='' && $path==$data['path']){
                                $current_api = $data;
                            }
                            $data = [
                                'group' => $data['group'],
                                'name' => $data['name'],
                                'path' => $data['path'],
                            ];
                            if (!$data['group']){
                                $menu_list[0][] = $data;
                            }
                            else{
                                $menu_list[md5($data['group'])][] = $data;
                            }
                        }
                    }
                }
            }
        }
    }
}
if (!$current_api){
    $current_api = $first_api;
}

//模板文件存放在 /template/api_docs.php
return result('api_docs', [
    //非必须的参数如果没有则不会返回此字段
    'api_info' => $current_api,
]);

function showVisitApiDosLoginUI(){
    echo '<form action="/api_docs" method="post" style="padding-top: 30px;text-align: center;">
                <p><input type="password" name="passport" placeholder="Enter passport" style="font-size: 22px;" required></p>
                <p><button type="submit" style="font-size: 22px;">Login</button> </p>
             </form>';
    exit;
}

//        [
//            'group' => '组名',
//            'version' => ['所属版本1','所属版本2'],
//            'path' => '文件路径',
//            'name' => '接口名',
//            'desc' => '接口描述',
//            'uri' => '接口地址',
//            'return' => '返回数据类型',
//            'exception' => '异常状态列举',
//            'params' => [
//                'data_type' => '数据类型',
//                'param_key' => '参数字段名',
//                'param_name' => '参数名',
//                'required' => '是否必需？',
//                'remark' => '参数描述',
//            ],
//        ],
function parseApiAnnotation($file){
    global $version_list;
    $keyword = input::I()->get_trim('keyword', '');
    if ($keyword){
        $in_search = false;
    }
    else{
        $in_search = true;
    }

    $content = file_get_contents($file);
    preg_match('/\/\*\*([\s\S]+?)\*\//i', $content, $matches);
    if ($matches && isset($matches[1])) {
        $content = $matches[1];
        preg_match('/@name\s+([^@]*)/i', $content, $matches);
        if ($matches && isset($matches[1])) {
            $matches[1] = preg_replace('/\*/', '', $matches[1]);
            $data = [
                'name' => trim($matches[1])
            ];
            if ($data['name']==''){
                return null;
            }
            if (!$in_search && strpos($data['name'], $keyword)!==false){
                $in_search = true;
            }
            preg_match('/@group\s+([^@]*)/i', $content, $matches);
            if ($matches && isset($matches[1])) {
                $matches[1] = preg_replace('/\*/', '', $matches[1]);
                $data['group'] = trim($matches[1]);
                if (!$in_search && strpos($data['group'], $keyword)!==false){
                    $in_search = true;
                }
            }
            else{
                $data['group'] = '';
            }
            preg_match('/@version\s*([^@]*)/i', $content, $matches);
            if ($matches && isset($matches[1])) {
                $matches[1] = preg_replace('/\*/', '', $matches[1]);
                $data['version'] = preg_split('/\s+/', trim($matches[1]));
                $version_list = array_filter(array_unique(array_merge($version_list, $data['version'])));
            }
            else{
                $data['version'] = [];
            }
            preg_match('/@desc\s+([^@]*)/i', $content, $matches);
            if ($matches && isset($matches[1])) {
                $matches[1] = preg_replace('/\*/', '', $matches[1]);
                $data['desc'] = trim($matches[1]);
                if (!$in_search && strpos($data['desc'], $keyword)!==false){
                    $in_search = true;
                }
            }
            else{
                $data['desc'] = '';
            }
            preg_match('/@uri\s+([^@]*)/i', $content, $matches);
            if ($matches && isset($matches[1])) {
                $matches[1] = preg_replace('/\*/', '', $matches[1]);
                $data['uri'] = trim($matches[1]);
                if (!$in_search && strpos($data['uri'], $keyword)!==false){
                    $in_search = true;
                }
            }
            else{
                $data['uri'] = '';
            }
            preg_match('/@method\s+([^@]*)/i', $content, $matches);
            if ($matches && isset($matches[1])) {
                $matches[1] = preg_replace('/\*/', '', $matches[1]);
                $data['method'] = trim($matches[1]);
                if (!$in_search && strpos($data['method'], $keyword)!==false){
                    $in_search = true;
                }
            }
            else{
                $data['method'] = '';
            }
            preg_match('/@return\s+([^@]+)/i', $content, $matches);
            if ($matches && isset($matches[1])) {
                $data['return'] = preg_replace('/\s*\*/', "\r\n", trim($matches[1]));
                if (!$in_search && strpos($data['return'], $keyword)!==false){
                    $in_search = true;
                }
            }
            else{
                $data['return'] = '';
            }
            preg_match('/@exception\s+([^@]+)/i', $content, $matches);
            if ($matches && isset($matches[1])) {
                $data['exception'] = preg_replace('/\s*\*/', "\r\n", trim($matches[1]));
                if (!$in_search && strpos($data['exception'], $keyword)!==false){
                    $in_search = true;
                }
            }
            else{
                $data['exception'] = '';
            }
            preg_match_all('/@param\s+(.+)/i', $content, $matches);
            if ($matches && isset($matches[1])) {
                $data['params'] = $matches[1];
                foreach ($data['params'] as $key => $value){
//                'data_type' => '数据类型',
//                'param_key' => '参数字段名',
//                'param_name' => '参数名',
//                'required' => '是否必需？',
//                'remark' => '参数描述',
                    if (!$in_search && strpos($value, $keyword)!==false){
                        $in_search = true;
                    }
                    $tmp = preg_split('/\s+/', trim($value));
                    $data['params'][$key] = [
                        'data_type' => isset($tmp[0]) ? $tmp[0] : '',
                        'param_key' => isset($tmp[1]) ? $tmp[1] : '',
                        'param_name' => isset($tmp[2]) ? $tmp[2] : '',
                        'required' => isset($tmp[3]) ? $tmp[3] : '',
                        'remark' => isset($tmp[4]) ? $tmp[4] : '',
                    ];
                }
            }
            else{
                $data['param'] = '';
            }
            if ($in_search){
                return $data;
            }
        }
    }
    return null;
}