<?php
/*
 * 请求参数处理类
 * YiluPHP vision 1.0
 * User: Jim.Wu
 * Date: 19/10/04
 * Time: 20:33
 */

if(file_exists('input_extend.php')){
    include_once 'input_extend.php';
}
if(!trait_exists('input_extend')){
    trait input_extend{}
}

class input
{
    use input_extend;

    protected $_default_error_code = [
        'required' => CODE_REQUIRED_PARAM_ERROR,
        'numeric' => CODE_NUMERIC_PARAM_ERROR,
        'integer' => CODE_INTEGER_PARAM_ERROR,
        'string' => CODE_STRING_PARAM_ERROR,
        'array' => CODE_ARRAY_PARAM_ERROR,
        'email' => CODE_EMAIL_PARAM_ERROR,
        'json' => CODE_JSON_PARAM_ERROR,
        'string_min' => CODE_STRING_MIN_PARAM_ERROR,
        'string_max' => CODE_STRING_MAX_PARAM_ERROR,
        'numeric_min' => CODE_NUMERIC_MIN_PARAM_ERROR,
        'numeric_max' => CODE_NUMERIC_MAX_PARAM_ERROR,
        'equal' => CODE_EQUAL_PARAM_ERROR,
        'rsa_encrypt' => CODE_RSA_PARAM_ERROR,
    ];

    /**
     * @name 获取默认错误在数组中的键
     * @desc
     * @param string $rule 当前规则名
     * @param array $rules 针对当前参数，用户定义的所有规则
     * @return string
     */
    private function _get_default_error_key($rule, &$rules)
    {
        if($rule =='min'){
            if(in_array('string', $rules)){
                $default_error_key = 'string_min';
            }
            if(in_array('numeric', $rules) || in_array('integer', $rules)){
                $default_error_key = 'numeric_min';
            }
        }
        else if($rule =='max'){
            if(in_array('string', $rules)){
                $default_error_key = 'string_max';
            }
            if(in_array('numeric', $rules) || in_array('integer', $rules)){
                $default_error_key = 'numeric_max';
            }
        }
        if(empty($default_error_key)){
            $default_error_key = $rule;
        }

        unset($rule);
        return $default_error_key;
    }

    /**
     * @name 获取一个参数的错误码
     * @desc
     * @param string $key 参数名
     * @param string $rule 当前规则名
     * @param array $rules 针对当前参数，用户定义的所有规则
     * @param array $error_code 用户定义的错误码
     * @return string
     */
    private function _error_code($key, $rule, &$rules, &$error_code=[])
    {
        $default_error_key = $this->_get_default_error_key($rule, $rules);

        $code = 100;
        if(isset($error_code[ $key.'.'.$rule ])){
            $code = $error_code[ $key.'.'.$rule ];
        }
        else if(isset( $error_code[ $key.'.*' ])){
            $code = $error_code[ $key.'.*' ];
        }
        else if (isset($this->_error_code[ $default_error_key ])){
            $code = $this->_error_code[ $default_error_key ];
        }

        unset($key, $rule, $default_error_key);
        return $code;
    }

    /**
     * @name 获取一个参数的错误提示语
     * @desc
     * @param string $key 参数名
     * @param string $rule 当前规则名
     * @param array $rules 针对当前参数，用户定义的所有规则
     * @param array $error_message 用户定义的错误提示语
     * @return string
     */
    private function _error_msg($key, $rule, &$rules, &$error_message=[])
    {
        global $app;
        $default_msg = [
            'required' => $app->lang('parameter_error_xxx', ['field' => $key]),
            'numeric' => $app->lang('xxx_parameter_must_be_numeric', ['field' => $key]),
            'integer' => $app->lang('xxx_parameter_must_be_a_integer', ['field' => $key]),
            'string' => $app->lang('xxx_parameter_must_be_string', ['field' => $key]),
            'array' => $app->lang('xxx_parameter_must_be_array', ['field' => $key]),
            'email' => $app->lang('xxx_parameter_must_be_email', ['field' => $key]),
            'json' => $app->lang('xxx_parameter_must_be_json', ['field' => $key]),
            'string_min' => $app->lang('xxx_parameter_string_min_error', ['field' => $key]),
            'string_max' => $app->lang('xxx_parameter_string_max_error', ['field' => $key]),
            'numeric_min' => $app->lang('xxx_parameter_numeric_min_error', ['field' => $key]),
            'numeric_max' => $app->lang('xxx_parameter_numeric_max_error', ['field' => $key]),
            'equal' => $app->lang('xxx_parameter_equal_error', ['field' => $key]),
            'rsa_encrypt' => $app->lang('xxx_parameter_rsa_encrypt_error', ['field' => $key]),
        ];
        $default_error_key = $this->_get_default_error_key($rule, $rules);

        $msg = '';
        if(isset($error_message[ $key.'.'.$rule ])){
            $msg = $error_message[ $key.'.'.$rule ];
        }
        else if(isset( $error_message[ $key.'.*' ])){
            $msg = $error_message[ $key.'.*' ];
        }
        else if (isset($default_msg[ $default_error_key ])){
            $msg = $default_msg[ $default_error_key ];
        }
        $msg == '' && $msg = $app->lang('parameter_error_xxx', ['field' => $key]);

        $min = $max = $equal_param = '';
        foreach ($rules as $item){
            $tmp= explode(':', $item, 2);
            if($tmp[0]=='min'){
                $min = $item;
            }
            if($tmp[0]=='max'){
                $max = $item;
            }
            if($tmp[0]=='equal' && count($tmp)>1){
                $equal_param = $tmp[1];
            }
            unset($tmp);
        }

        if($rule=='min' && $min!=''){
            $min = explode(':', $min,2);
            if (isset($min[1])){
                $min = floatval($min[1]);
            }
            else{
                $min = '';
            }
            $msg = str_replace('{{min}}', $min, $msg);
            unset($min);
        }
        if($rule=='max' && $max!=''){
            $max = explode(':', $max,2);
            if (isset($max[1])){
                $max = floatval($max[1]);
            }
            else{
                $max = '';
            }
            $msg = str_replace('{{max}}', $max, $msg);
            unset($max);
        }

        if($rule=='equal' && $equal_param!=''){
            $msg = str_replace('{{param}}', $equal_param, $msg);
            unset($min);
        }
        unset($key, $rule, $default_error_key, $min, $max, $default_msg);
        return $msg;
    }

    /**
     * @name 去掉字符串前后的空格
     * @param mixed $value 可接受任务类型的值
     * @return mixed
     */
    private function _trim_value($value)
    {
        if (is_string($value)){
            return trim($value);
        }
        else if (is_array($value)){
            foreach ($value as $key=>$val){
                $value[$key] = $this->_trim_value($val);
            }
        }
        else{
            return $value;
        }
    }

    /**
     * @name 检查一个参数的值是否为整数
     * @desc
     * @param string $key 参数名
     * @param string $value 值
     * @param array $rules 针对当前参数，用户定义的所有规则
     * @param array $error_message 用户定义的错误提示语
     * @return string
     */
    private function _check_integer($key, $value, &$rules, &$error_message=[])
    {
        unset($key);
        return preg_match('/^[-\d]+$/', $value);
    }

    /**
     * @name 检查一个参数的值是否为字符串
     * @desc
     * @param string $key 参数名
     * @param string $value 值
     * @param array $rules 针对当前参数，用户定义的所有规则
     * @param array $error_message 用户定义的错误提示语
     * @return string
     */
    private function _check_string($key, $value, &$rules, &$error_message=[])
    {
        unset($key);
        return is_string($value);
    }

    /**
     * @name 检查一个参数的值是否为数组
     * @desc
     * @param string $key 参数名
     * @param string $value 值
     * @param array $rules 针对当前参数，用户定义的所有规则
     * @param array $error_message 用户定义的错误提示语
     * @return string
     */
    private function _check_array($key, $value, &$rules, &$error_message=[])
    {
        unset($key);
        return is_array($value);
    }

    /**
     * @name 检查一个参数的值是否为Email
     * @desc
     * @param string $key 参数名
     * @param string $value 值
     * @param array $rules 针对当前参数，用户定义的所有规则
     * @param array $error_message 用户定义的错误提示语
     * @return string
     */
    private function _check_email($key, $value, &$rules, &$error_message=[])
    {
        unset($key);
        return is_email($value);
    }

    /**
     * @name 检查一个参数的值是否为JSON
     * @desc
     * @param string $key 参数名
     * @param string $value 值
     * @param array $rules 针对当前参数，用户定义的所有规则
     * @param array $error_message 用户定义的错误提示语
     * @return string
     */
    private function _check_json($key, $value, &$rules, &$error_message=[])
    {
        unset($key);
        $value = json_decode($value, true);
        if($value && is_array($value)){
            unset($value);
            return true;
        }
        unset($value);
        return false;
    }

    /**
     * @name 检查一个参数的值是否符合最小/短值要求
     * @desc
     * @param string $key 参数名
     * @param string $value 值
     * @param array $rules 针对当前参数，用户定义的所有规则
     * @param array $error_message 用户定义的错误提示语
     * @return string
     */
    private function _check_min($key, $value, &$rules, &$error_message=[])
    {
        unset($key);
        $min = '';
        foreach ($rules as $item){
            if(strpos($item, 'min')===0){
                $min = $item;
            }
        }
        if(in_array('string', $rules)){
            $min = explode(':', $min, 2);
            if (isset($min[1])){
                $min = intval($min[1]);
                if(mb_strlen($value)<$min){
                    unset($min, $value);
                    return false;
                }
                return true;
            }
            return true;
        }
        if(in_array('numeric', $rules) || in_array('integer', $rules)){
            $min = explode(':', $min,2);
            if (isset($min[1])){
                $min = floatval($min[1]);
                if($value<$min){
                    unset($min, $value);
                    return false;
                }
                return true;
            }
            return true;
        }
        return true;
    }

    /**
     * @name 检查一个参数的值是否符合最大/长值要求
     * @desc
     * @param string $key 参数名
     * @param string $value 值
     * @param array $rules 针对当前参数，用户定义的所有规则
     * @param array $error_message 用户定义的错误提示语
     * @return string
     */
    private function _check_max($key, $value, &$rules, &$error_message=[])
    {
        unset($key);
        $max = '';
        foreach ($rules as $item){
            if(strpos($item, 'max')===0){
                $max = $item;
            }
        }
        if(in_array('string', $rules)){
            $max = explode(':', $max,2);
            if (isset($max[1])){
                $max = intval($max[1]);
                if(mb_strlen($value)>$max){
                    unset($max, $value);
                    return false;
                }
                return true;
            }
            return true;
        }
        if(in_array('numeric', $rules) || in_array('integer', $rules)){
            $max = explode(':', $max,2);
            if (isset($max[1])){
                $max = floatval($max[1]);
                if($value>$max){
                    unset($max, $value);
                    return false;
                }
                return true;
            }
            return true;
        }
        return true;
    }

    /**
     * @name 检查一个参数的值是否为数字
     * @desc
     * @param string $key 参数名
     * @param string $value 值
     * @param array $rules 针对当前参数，用户定义的所有规则
     * @param array $error_message 用户定义的错误提示语
     * @return string
     */
    private function _check_numeric($key, $value, &$rules, &$error_message=[])
    {
        unset($key);
        return preg_match('/^[-\.\d]+$/', $value);
    }

    /**
     * @name 检查一个参数的值与另一个参数的值是否相同
     * @desc
     * @param string $key 参数名
     * @param string $value 值
     * @param array $rules 针对当前参数，用户定义的所有规则
     * @param array $error_message 用户定义的错误提示语
     * @return string
     */
    private function _check_equal($key, $value, &$rules, &$error_message=[])
    {
        $param_name = '';
        foreach ($rules as $item){
            if(strpos($item, 'equal:')===0){
                $tmp = explode(':', $item,2);
                if (count($tmp)>1){
                    $param_name = $tmp[1];
                }
                unset($tmp);
            }
        }
        if (!$param_name){
            unset($param_name);
            return false;
        }
        $param_value = isset($_REQUEST[$param_name]) ? $_REQUEST[$param_name] : null;
        $res = $value===$param_value;
        unset($key, $value, $param_name, $param_value);
        return $res;
    }

    /**
     * @name 验证表单提交过来参数及值
     * @desc
     * @param array $rules 参数名
     * @param array $error_message 自定义错误信息
     * @param array $error_code 自定义错误码
     * @return array string
     */
    public function validate($rules, $error_message=[], $error_code=[])
    {
        global $app;
        if(empty($rules) || !is_array($rules)){
            unset($rules, $error_message, $error_code);
            return [];
        }
        $values = [];
        foreach ($rules as $key => $rule){
            if(empty($rule)){
                continue;
            }
            $rule_arr = explode('|', $rule);
            //检查是否为必须的参数
            if (in_array('required', $rule_arr) && !isset($_REQUEST[$key])){
                return_code(
                    $this->_error_code($key, 'required', $rule_arr, $error_code),
                    $this->_error_msg($key, 'required', $rule_arr, $error_message)
                );
            }
            $val = $this->request($key);
            //去掉前后空格后再检验
            if (in_array('trim', $rule_arr)){
                $val = $this->_trim_value($val);
            }
            //非必须的参数,当没有此参数时跳过检查
            if (!in_array('required', $rule_arr) && $val===null) {
                continue;
            }
            //如果需要RSA解密,则先解密
            if (in_array('rsa_encrypt', $rule_arr)){
                if(empty($GLOBALS['config']['rsa_private_key'])){
                    return_code(
                        $this->_error_code($key, 'rsa_private_key', $rule_arr, $error_code),
                        $app->lang('decryption_failed_no_private_key')
                    );
                }
                $private_key = $GLOBALS['config']['rsa_private_key'];
                $val = str_replace('%2B', '+', $val);
                $tmp = '';

                //rsa解密
                if(!openssl_private_decrypt(base64_decode($val), $tmp, $private_key)){
                    return_code(
                        $this->_error_code($key, 'rsa_private_key', $rule_arr, $error_code),
                        $this->_error_msg($key, 'rsa_private_key', $rule_arr, $error_message)
                    );
                }
                $val = $tmp;
                unset($tmp);
            }
            foreach ($rule_arr as $item_rule){
                if (empty($item_rule)){
                    continue;
                }
                //非必须的参数,当没有此参数或内容为空字符串时跳过检查
                if (!in_array('required', $rule_arr) && ($val===null || $val==='')) {
                    continue;
                }
                if(!in_array($item_rule, ['required', 'trim', 'return', 'rsa_encrypt'])){
                    $split_rule = explode(':', $item_rule,2);
                    if(method_exists($this, '_check_'.$split_rule[0])){
                        $method = '_check_'.$split_rule[0];
                        if(!$this->$method($key, $val, $rule_arr, $error_message)){
                            if (!(!in_array('required', $rule_arr) && $val===null)) {
                                return_code(
                                    $this->_error_code($key, $split_rule[0], $rule_arr, $error_code),
                                    $this->_error_msg($key, $split_rule[0], $rule_arr, $error_message)
                                );
                            }
                        }
                        unset( $method);
                    }
                    else if (function_exists($item_rule)){
                        //自定义的校验函数，如果校验通过则返回true，否则返回大于零的错误码
                        if(!$item_rule($key, $val, $rule_arr, $error_message)){
                            if (!(!in_array('required', $rule_arr) && $val===null)) {
                                return_code(
                                    $this->_error_code($key, $split_rule[0], $rule_arr, $error_code),
                                    $this->_error_msg($key, $split_rule[0], $rule_arr, $error_message)
                                );
                            }
                        }
                    }
                    else{
                        return_code(CODE_PARAM_ERROR, $app->lang('unknown_validation_rule_for_parameter_xxx', ['field' => $key]).$item_rule );
                    }
                    unset($split_rule);
                }
            }
            if (in_array('return', $rule_arr)){
                $values[$key] = $val;
            }
            unset($item_rule, $val, $rule_arr);
        }
        unset($rules, $error_message, $error_code, $key, $rule);
        return $values;
    }

    /**
     * @name 获取GET方法提交过来参数值，去掉前后的空格
     * @desc
     * @param string $key 参数名
     * @param string $default 如果参数没有值，则以此为默认值
     * @return string
     */
    public function get_trim($key, $default=null)
    {
        $val = isset($_GET[$key]) ? trim($_GET[$key]) : null;
        if(($val===null || $val==='') && $default!==null && $default!==''){
            return $default;
        }
        return $val;
    }

    /**
     * @name 获取POST方法提交过来参数值，去掉前后的空格
     * @desc
     * @param string $key 参数名
     * @param string $default 如果参数没有值，则以此为默认值
     * @return string
     */
    public function post_trim($key, $default=null)
    {
        $val = isset($_POST[$key]) ? trim($_POST[$key]) : null;
        if(($val===null || $val==='') && $default!==null && $default!==''){
            return $default;
        }
        return $val;
    }

    /**
     * @name 获取POST或GET方法提交过来参数值，去掉前后的空格
     * @desc
     * @param string $key 参数名
     * @param string $default 如果参数没有值，则以此为默认值
     * @return string
     */
    public function request_trim($key, $default=null)
    {
        $val = isset($_REQUEST[$key]) ? trim($_REQUEST[$key]) : null;
        if(($val===null || $val==='') && $default!==null && $default!==''){
            return $default;
        }
        return $val;
    }

    /**
     * @name 获取GET方法提交过来参数值
     * @desc
     * @param string $key 参数名
     * @param string $default 如果参数没有值，则以此为默认值
     * @return string
     */
    public function get($key, $default=null)
    {
        $val = isset($_GET[$key]) ? $_GET[$key] : null;
        if(($val===null || $val==='') && $default!==null && $default!==''){
            return $default;
        }
        return $val;
    }

    /**
     * @name 获取POST方法提交过来参数值
     * @desc
     * @param string $key 参数名
     * @param string $default 如果参数没有值，则以此为默认值
     * @return string
     */
    public function post($key, $default=null)
    {
        $val = isset($_POST[$key]) ? $_POST[$key] : null;
        if(($val===null || $val==='') && $default!==null && $default!==''){
            return $default;
        }
        return $val;
    }

    /**
     * @name 获取POST或GET方法提交过来参数值
     * @desc
     * @param string $key 参数名
     * @param string $default 如果参数没有值，则以此为默认值
     * @return string
     */
    public function request($key, $default=null)
    {
        $val = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
        if(($val===null || $val==='') && $default!==null && $default!==''){
            return $default;
        }
        return $val;
    }

    /**
     * @name 获取GET方法提交过来参数值(整数)
     * @desc
     * @param string $key 参数名
     * @param string $default 如果参数没有值，则以此为默认值
     * @return string
     */
    public function get_int($key, $default=null)
    {
        $val = isset($_GET[$key]) ? $_GET[$key] : null;
        if($val===null || $val===''){
            return $default;
        }
        return intval($val);
    }

    /**
     * @name 获取POST方法提交过来参数值(整数)
     * @desc
     * @param string $key 参数名
     * @param string $default 如果参数没有值，则以此为默认值
     * @return string
     */
    public function post_int($key, $default=null)
    {
        $val = isset($_POST[$key]) ? $_POST[$key] : null;
        if($val===null || $val===''){
            return $default;
        }
        return intval($val);
    }

    /**
     * @name 获取POST或GET方法提交过来参数值(整数)
     * @desc
     * @param string $key 参数名
     * @param string $default 如果参数没有值，则以此为默认值
     * @return string
     */
    public function request_int($key, $default=null)
    {
        $val = isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
        if($val===null || $val===''){
            return $default;
        }
        return intval($val);
    }
}
