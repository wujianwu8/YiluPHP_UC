<?php
/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */

require_once(__DIR__."/ErrorCase.class.php");
class Recorder{
    private static $data;
    private $inc;
    private $error;

    public function __construct(){
        $this->error = new ErrorCase();

        //-------读取配置文件
        if(empty($GLOBALS['config']['qq_connect'])){
            $this->error->showError("20001");
        }
        $this->inc = $GLOBALS['config']['qq_connect'];

        if(empty($_SESSION['QC_userData']) || !is_array($_SESSION['QC_userData'])){
            self::$data = array();
        }else{
            self::$data = $_SESSION['QC_userData'];
        }
    }

    public function write($name,$value){
        self::$data[$name] = $value;
        $this->save_session();
    }

    public function read($name){
        if(isset(self::$data[$name])){
            return self::$data[$name];
        }
        return null;
    }

    public function delete($name){
        if(isset(self::$data[$name])){
            unset(self::$data[$name]);
            $this->save_session();
        }
    }

    function save_session(){
        $_SESSION['QC_userData'] = self::$data;
    }

    public function readInc($name){
        if(empty($this->inc[$name])){
            return null;
        }else{
            return $this->inc[$name];
        }
    }
}
