<?php
/*
 * 用户的配置文件
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/11/27
 * Time: 19:21
 */

$content = '<?php
/*
 * 为了方便开发时,在开发工具中可以跳转跟踪函数/方法/类而存在的
 * 可以CLI模式运行根目录下的auto_jump.php文件自动生成该文件的内容
 * php auto_jump.php
 */

trait useful_cheat{
    public function useful_cheat()
    {
        $time = '.time().';
        if(time()-$time>=3 && in_array(env(), [\'local\',\'dev\'])){
            if(file_exists(\'../auto_jump.php\')){
                require_once \'../auto_jump.php\';
            }
        }
        if(false){';
$content .= "\r\n";
$dirs = scandir(__DIR__);
$dir_arr = [
    'helper','hook','lib','library','logic','model','tool','service','provider','route','exception','plugin',
    'helpers','hooks','libs','libraries','logics','models','tools','services','providers','routes','exceptions','plugins'
];
foreach ($dirs as $dir) {
    if (!is_dir(__DIR__ . '/'.$dir) || substr($dir,0,1)=='.' || empty(substr($dir,0,1))){
        continue;
    }
    if (!in_array(strtolower($dir), $dir_arr)){
        continue;
    }
    $file = scandir(__DIR__ . '/'.$dir);
    foreach ($file as $value) {
        $name = __DIR__ . '/'.$dir.'/' . $value;
        if (is_file($name)) {
            if (strtolower(substr($value, -4)) === '.php') {
                $class_name = substr($value, 0, -4);
                $content .= "             \$this->" . $class_name . " = new " . $class_name . "();\r\n";
            }
        }
    }
}
$content .= "        }
    }
}\r\n";

if(is_writable(__DIR__.'/public/useful_cheat.php')) {
    file_put_contents(__DIR__ . '/public/useful_cheat.php', $content);
}
else if(function_exists('env') && function_exists('write_applog')){
    /*为了更好的开发体验，请把这个文件的写权限打开，
      并确保在你的开发目录中，这个文件的内容中包含了所有help目录里的类
    */
    write_applog('NOTICE', 'For your better development experience, please open the write permission for this file:'.
        "\nchmod 777 ".__DIR__.'/public/useful_cheat.php'.
        "\nAnd make sure that all classes in the \"help\" directory are included in the contents of \"".
        __DIR__.'/public/useful_cheat.php" file in your development directory.');
}
if(empty(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1))) {
    echo "\r\nDone\r\n\r\n";
}