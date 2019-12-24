<?php
/*
 * 为了方便开发时,在开发工具中可以跳转跟踪函数/方法/类而存在的
 * 可以CLI模式运行根目录下的auto_jump.php文件自动生成该文件的内容
 * php auto_jump.php
 */

trait useful_cheat{
    public function __construct()
    {
        $time = 1575972597;
        if(time()-$time>=3 && in_array(env(), ['local','dev'])){
            if(file_exists('../auto_jump.php')){
                require_once '../auto_jump.php';
            }
        }
        if(false){
             $this->curl = new curl();
             $this->helper_demo = new helper_demo();
             $this->input = new input();
             $this->pager = new pager();
             $this->hook_csrf = new hook_csrf();
             $this->hook_header_setter = new hook_header_setter();
             $this->model = new model();
             $this->model_demo = new model_demo();
        }
    }
}
