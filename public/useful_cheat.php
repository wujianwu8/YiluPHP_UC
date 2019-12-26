<?php
/*
 * 为了方便开发时,在开发工具中可以跳转跟踪函数/方法/类而存在的
 * 可以CLI模式运行根目录下的auto_jump.php文件自动生成该文件的内容
 * php auto_jump.php
 */

trait useful_cheat{
    public function useful_cheat()
    {
        $time = 1577263933;
        if(time()-$time>=3 && in_array(env(), ['local','dev'])){
            if(file_exists('../auto_jump.php')){
                require_once '../auto_jump.php';
            }
        }
        if(false){
             $this->QRcode = new QRcode();
             $this->curl = new curl();
             $this->file = new file();
             $this->helper_demo = new helper_demo();
             $this->input = new input();
             $this->oauth = new oauth();
             $this->oauth_alipay = new oauth_alipay();
             $this->oauth_linkedin = new oauth_linkedin();
             $this->oauth_qq = new oauth_qq();
             $this->oauth_wechat = new oauth_wechat();
             $this->pager = new pager();
             $this->qq_connect = new qq_connect();
             $this->uuid = new uuid();
             $this->hook_csrf = new hook_csrf();
             $this->hook_header_setter = new hook_header_setter();
             $this->hook_internal = new hook_internal();
             $this->hook_route_auth = new hook_route_auth();
             $this->lib_address = new lib_address();
             $this->lib_ip = new lib_ip();
             $this->logic_application = new logic_application();
             $this->logic_menus = new logic_menus();
             $this->logic_permission = new logic_permission();
             $this->logic_role = new logic_role();
             $this->logic_user = new logic_user();
             $this->model = new model();
             $this->model_application = new model_application();
             $this->model_email_code_record = new model_email_code_record();
             $this->model_language_project = new model_language_project();
             $this->model_language_value = new model_language_value();
             $this->model_menus = new model_menus();
             $this->model_permission = new model_permission();
             $this->model_role = new model_role();
             $this->model_role_permission = new model_role_permission();
             $this->model_sms_record = new model_sms_record();
             $this->model_try_to_sign_in = new model_try_to_sign_in();
             $this->model_user = new model_user();
             $this->model_user_complaint = new model_user_complaint();
             $this->model_user_feedback = new model_user_feedback();
             $this->model_user_identity = new model_user_identity();
             $this->model_user_permission = new model_user_permission();
             $this->model_user_role = new model_user_role();
             $this->tool_file_uploader = new tool_file_uploader();
             $this->tool_mailer = new tool_mailer();
             $this->tool_oss = new tool_oss();
             $this->tool_sms = new tool_sms();
        }
    }
}
