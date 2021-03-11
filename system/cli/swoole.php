<?php
/*
 * Swoole http 服务端启动文件
 * 启动方式：php yilu swoole [start|stop]
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * Date: 2021/01/27
 * Time: 21:45
 */

if (empty($config['swoole']['http_server_host'])){
    $host = '0.0.0.0';
}
else{
    $host = $config['swoole']['http_server_host'];
}
if (empty($config['swoole']['http_server_port'])){
    $port = '9501';
}
else{
    $port = $config['swoole']['http_server_port'];
}

$http = new Swoole\Http\Server($host, $port);

$http->on('request', function ($request, $response) {
    //$request 与 $response的文档：https://wiki.swoole.com/wiki/page/332.html
    if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
        $response->end();
        return;
    }

    if (!defined('SWOOLE_HTTP_SERVER')){
        define('SWOOLE_HTTP_SERVER', true);
    }

    $_GET = $_POST = $_REQUEST = [];
    if (!empty($request->get) && is_array($request->get)){
        foreach ($request->get as $key => $value){
            $_REQUEST[$key] = $_GET[$key] = $value;
        }
    }
    if (!empty($request->post) && is_array($request->post)){
        foreach ($request->post as $key => $value){
            $_REQUEST[$key] = $_POST[$key] = $value;
        }
    }
    foreach ($request->header as $key => $value){
        $key = 'HTTP_'.strtoupper(str_replace('-','_',$key));
        $_SERVER[$key] = $value;
    }
    foreach ($request->server as $key => $value){
        $key = strtoupper($key);
        $_SERVER[$key] = $value;
    }

    if (class_exists('YiluPHP')){
        YiluPHP::$swoole_data = [];
    }

    if (!defined('APP_PATH')){
        //项目的根目录，最后包含一个斜杠
        define('APP_PATH', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR );
    }
    global $config;
    if (empty($config)) {
        $config = require(APP_PATH.'config'.DIRECTORY_SEPARATOR.'app.php');
    }

    $static_extension = ['js','jpg','css','jpeg','html','shtml','png','ico','gif','bmp','svga','eot','svg','ttf','woff','woff2','zip','rar','tgz','gz'];
    if (!empty($config['swoole']['static_extension'])){
        $tmp = explode(',', $config['swoole']['static_extension']);
        foreach ($tmp as $value){
            $value = strtolower(trim($value));
            if ($value!=''){
                $static_extension[] = $value;
            }
        }
    }

    $path_info = pathinfo($_SERVER['REQUEST_URI']);
    //静态资源直接返回
    if (isset($path_info['extension']) && in_array(strtolower($path_info['extension']), $static_extension)){
        $filename = APP_PATH.'static'.$_SERVER['REQUEST_URI'];
        if (file_exists($filename)) {
            $response->sendfile($filename);
        }
        else{
            $response->status(404, 'Not Found');
            $response->end('');
            return;
        }
    }
    else {
        $response_data = include APP_PATH . 'public' . DIRECTORY_SEPARATOR . 'index.php';
        if (isset($response_data['cookies'])) {
            foreach ($response_data['cookies'] as $item) {
                //$response->cookie("cookie名", "cookie值", "有效期时间戳", 'path', '域名', Secure默认为false, HttpOnly默认为false);
                $expire = isset($item['expire']) ? intval($item['expire']) : 0;
                $path = isset($item['path']) ? $item['path'] : '';
                $domain = isset($item['domain']) ? $item['domain'] : '';
                $secure = isset($item['secure']) ? $item['secure'] : false;
                $http_only = isset($item['http_only']) ? $item['http_only'] : false;
                $response->cookie($item['name'], $item['value'], $expire, $path, $domain, $secure, $http_only);
            }
        }

        if (isset($response_data['headers'])) {
            foreach ($response_data['headers'] as $key => $value) {
                $response->header($key, $value);
            }
        }

        if (isset($response_data['redirect'])) {
            $response->redirect($response_data['redirect']['url'], $response_data['redirect']['http_code']);
            return;
        }

        if (!empty($response_data['send_file']['path'])) {
            if (file_exists($response_data['send_file']['path'])) {
                $response->sendfile($response_data['send_file']['path']);
                return;
            }
            else{
                $response->status(404, 'Not Found');
                $response->end('');
                return;
            }
        }

        if (isset($response_data['status'])) {
            $reason = isset($response_data['status']['reason']) ? $response_data['status']['reason'] : '';
            $response->status(intval($response_data['status']['http_status_code']), $reason);
        }

        $response->header("Content-Type", "text/html; charset=utf-8");
        $response->end(isset($response_data['html']) ? $response_data['html'] : '');
    }

});

if (!empty($config['swoole']['http_server_options'])) {
    $http->set($config['swoole']['http_server_options']);
}

echo "启动swoole http server成功\r\n";
echo "host：$host  端口号：$port\r\n";
$http->start();

