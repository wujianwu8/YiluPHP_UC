<?php
/**
 * 邮件发送类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/233
 * Time: 20:56
 * 使用方法，先配置邮件平台的信息
    tool_mailer::I()->to_alias = 'bbb';
    tool_mailer::I()->to_email = 'bambooner.wu@qq.com';
    tool_mailer::I()->subject = 'subject title';
    tool_mailer::I()->html_body = 'Hello friend.';
    tool_mailer::I()->auto_send();
 */


use AlibabaCloud\SDK\Dm\V20151123\Dm;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\SDK\Dm\V20151123\Models\SingleSendMailRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;

use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;
//use Dm\Request\V20151123 as Dm;

class tool_mailer extends base_class
{
    //SingleSendMail接口文档
    //https://help.aliyun.com/document_detail/29444.html

    //发信人邮件,如果使用阿里云,则一定要在控制台已经创建的发信地址
    public $from_email = '';

    //发信人名称
    public $from_alias = '';

    //如果使用阿里云,则是控制台创建的标签
    public $tag_name = '';

    //取值范围 0~1: 0 为随机账号；1 为指定发信地址。
    public $address_type = 1;

    //是否接收邮件回复,使用管理控制台中配置的回信地址（状态必须是验证通过）。
    public $reply_to_address = true;

    //收件人邮箱,可以给多个收件人发送邮件，收件人之间用逗号分开，最多100个地址。若调用模板批量发信建议使用BatchSendMailRequest方式
    public $to_email = '';

    //收件人名称
    public $to_alias = '';

    //邮件主题
    public $subject = '';

    //HtmlBody 邮件 html 正文，限制28K。
    public $html_body = '';

    //TextBody 邮件 text 正文，限制28K。
    public $text_body = '';

    //ClickTrace 取值范围 0~1: 1 为打开数据跟踪功能; 0 为关闭数据跟踪功能。该参数默认值为 0。
    public $click_trace = '1';


	public function __construct()
	{
	    if (empty($GLOBALS['config']['mailer']['usable'])){
            write_applog('ERROR','未配置发送邮件的平台');
            throw new Exception('未配置发送邮件的平台', CODE_EMAIL_PLAT_CONFIG_ERROR);
	        return false;
        }
        if (!is_array($GLOBALS['config']['mailer']['usable'])){
            write_applog('ERROR','发送邮件的可用平台配置必须为数组');
            throw new Exception('发送邮件的可用平台配置必须为数组', CODE_EMAIL_PLAT_CONFIG_ERROR);
            return false;
        }
        foreach ($GLOBALS['config']['mailer']['usable'] as $item) {
            if (empty($GLOBALS['config']['mailer'][$item])) {
                throw new Exception('发送邮件的可用平台必须配置相关信息：' . $item, CODE_EMAIL_PLAT_CONFIG_ERROR);
                return false;
            }
        }
	}

    /**
     * @name 重置邮件设置
     * @return bool
     */
    public function reset()
    {
        $this->from_email = $this->from_alias = $this->tag_name = $this->to_email = $this->to_alias
            = $this->subject = $this->html_body = '';
        $this->address_type = 1;
        $this->reply_to_address = true;
        return true;
    }

    /**
     * @name 以数组返回所有的邮件设置内容
     * @return array
     */
    public function all_params()
    {
        return [
            'from_email' => $this->from_email,
            'from_alias' => $this->from_alias,
            'to_email' => $this->to_email,
            'to_alias' => $this->to_alias,
            'subject' => $this->subject,
            'html_body' => $this->html_body,
            'text_body' => $this->text_body,
            'tag_name' => $this->tag_name,
            'address_type' => $this->address_type,
            'reply_to_address' => $this->reply_to_address,
            'click_trace' => $this->click_trace,
        ];
    }

    /**
     * @name 检查邮件的必要设置是否正确
     * @return bool
     */
    public function check_content()
    {
        if(trim($this->from_alias)==''){
            write_applog('ERROR', '发送邮件时缺少发件人名称:'.json_encode($this->all_params()));
            return false;
        }
        if(trim($this->from_email)==''){
            write_applog('ERROR', '发送邮件时缺少发件人邮箱地址:'.json_encode($this->all_params()));
            return false;
        }
        if(trim($this->to_email)==''){
            write_applog('ERROR', '发送邮件时缺少收件人邮箱地址:'.json_encode($this->all_params()));
            return false;
        }
        if(trim($this->to_alias)==''){
            write_applog('ERROR', '发送邮件时缺少收件人名称:'.json_encode($this->all_params()));
            return false;
        }
        if(trim($this->subject)==''){
            write_applog('ERROR', '发送邮件时缺少邮件标题:'.json_encode($this->all_params()));
            return false;
        }
        if(trim($this->html_body)=='' && trim($this->text_body)==''){
            write_applog('ERROR', '发送邮件时缺少邮件内容:'.json_encode($this->all_params()));
            return false;
        }
        $emails = explode(',', $this->to_email);
        $arr = $error = [];
        foreach($emails as $email){
            if(is_email(trim($email))){
                $arr[] = trim($email);
            }
            else{
                $error[] = trim($email);
            }
        }
        if(empty($arr)){
            write_applog('WARNING', '发送邮件时收件人邮箱错误:'.$this->to_email);
            unset($emails, $error, $arr);
            return false;
        }
        if(!empty($error)){
            write_applog('WARNING', '发送邮件时有的收件人邮箱错误:'.json_encode($error));
            unset($emails, $error, $arr);
            return false;
        }
        $this->to_email = implode(',', $arr);
        unset($emails, $error, $arr);
        return true;
    }

    /**
     * @name 自动选择一个邮件系统进行发邮件
     * @desc 从config中已经配置的邮件系统中自动选择一个进行发送
     * @return bool
     */
    public function auto_send()
    {
        if(!empty($GLOBALS['config']['mailer']['qq_email_use_phpmailer'])
            && in_array('phpmailer', $GLOBALS['config']['mailer']['usable'])){
            //提取出QQ邮箱
            $emails = explode(',', $this->to_email);
            $other_emails = [];
            foreach($emails as $email){
                $email = trim(strtolower($email));
                if(substr($email, -6)=='qq.com'){
                    $this->to_email = trim($email);
                    $this->send_by_phpmailer();
                }
                else{
                    $other_emails[] = trim($email);
                }
            }
            $this->to_email = implode(',', $other_emails);
            if(empty($this->to_email)){
                return true;
            }
        }

        if (count($GLOBALS['config']['mailer']['usable'])<=1){
            $plat_name = $GLOBALS['config']['mailer']['usable'][0];
        }
        else{
            $max_num = 0;
            foreach ($GLOBALS['config']['mailer']['usable'] as $item){
                if (!isset($GLOBALS['config']['mailer'][$item]['weight'])){
                    $max_num += 1;
                }
                else{
                    $GLOBALS['config']['mailer'][$item]['weight'] = intval($GLOBALS['config']['mailer'][$item]['weight']);
                    if ($GLOBALS['config']['mailer'][$item]>0){
                        $max_num += $GLOBALS['config']['mailer'][$item]['weight'];
                    }
                }
            }
            $use_num = rand(1, $max_num);
            $num = 1;
            foreach ($GLOBALS['config']['mailer']['usable'] as $item){
                if( $num<=$use_num && $use_num<$num+$GLOBALS['config']['mailer'][$item]['weight']){
                    $plat_name = $item;
                    break;
                }
                $num += $GLOBALS['config']['mailer'][$item]['weight'];
            }
            unset($max_num, $use_num, $num, $item);
        }
        if(empty($plat_name)){
            write_applog('ERROR', '未找到合适的发送邮件平台:'.json_encode($this->all_params()));
            return false;
        }
        $plat_name = 'send_by_'.$plat_name;
        return $this->$plat_name();
    }

    /**
     * @name 使用阿里云发邮件
     * @return bool
     */
    public function send_by_aliyun()
    {
        if(empty($GLOBALS['config']['mailer']['aliyun']['access_key_id'])){
            write_applog('ERROR', '未设置阿里云邮件推送的access_key_id:'.json_encode($this->all_params()));
            return false;
        }
        if(empty($GLOBALS['config']['mailer']['aliyun']['access_key_secret'])){
            write_applog('ERROR', '未设置阿里云邮件推送的access_key_secret:'.json_encode($this->all_params()));
            return false;
        }
        if(empty($GLOBALS['config']['mailer']['aliyun']['from_name'])){
            write_applog('ERROR', '未设置阿里云邮件推送的from_name:'.json_encode($this->all_params()));
            return false;
        }
        if(empty($GLOBALS['config']['mailer']['aliyun']['from_email'])){
            write_applog('ERROR', '未设置阿里云邮件推送的from_email:'.json_encode($this->all_params()));
            return false;
        }
        $this->from_alias = $GLOBALS['config']['mailer']['aliyun']['from_name'];
        $this->from_email = $GLOBALS['config']['mailer']['aliyun']['from_email'];
        if(!$this->check_content()){
            write_applog('ERROR', '发送邮件失败,必填项缺失:'.json_encode($this->all_params()));
            return false;
        }

        // 建议使用更安全的 STS 方式，更多鉴权访问方式请参见：https://help.aliyun.com/document_detail/311677.html。
        $config = new Config([
            // 必填，请确保代码运行环境设置了环境变量 ALIBABA_CLOUD_ACCESS_KEY_ID。
            'accessKeyId' => $GLOBALS['config']['mailer']['aliyun']['access_key_id'],
            // 必填，请确保代码运行环境设置了环境变量 ALIBABA_CLOUD_ACCESS_KEY_SECRET。
            'accessKeySecret' => $GLOBALS['config']['mailer']['aliyun']['access_key_secret'],
        ]);
        // Endpoint 请参考 https://api.aliyun.com/product/Dm
        $config->endpoint = "dm.aliyuncs.com";
        $client = new Dm($config);

        $params = [
            'accountName'            => $this->from_email,  //管理控制台中配置的发信地址
            'addressType'            => 0,  //地址类型。取值：0：为随机账号，1：为发信地址
//            'clickTrace'             => 'ClickTrace',  //1：为打开数据跟踪功能，0（默认）：为关闭数据跟踪功能。
            'fromAlias'              => $this->from_alias,  //发信人昵称，长度小于 15 个字符。例如：发信人昵称设置为”小红”，发信地址为 test***@example.net，收信人看到的发信地址为“小红”test***@example.net。
            'htmlBody'               => $this->html_body,  //邮件 html 正文，限制 28K。注意：HtmlBody 和 TextBody 是针对不同类型的邮件内容，两者必须传其一。
//            'ownerId'                => 'OwnerId',
//            'replyAddress'           => 'ReplyAddress',  //回信地址
//            'replyAddressAlias'      => 'ReplyAddressAlias',  //回信地址昵称
            'replyToAddress'         => false,  //是否启用管理控制台中配置好回信地址（状态须验证通过），取值范围是字符串 true 或者 false（不是 bool 值）。
//            'resourceOwnerAccount'   => 'ResourceOwnerAccount',
//            'resourceOwnerId'        => 'ResourceOwnerId',
            'subject'                => $this->subject,  //邮件主题
            'tagName'                => $this->tag_name,  //在邮件推送控制台创建的标签，用于分类所发送的邮件批次，可以通过标签来查询每批邮件的发送情况，另外如果开启邮件跟踪功能，发信必须使用邮件标签。
            'textBody'               => $this->html_body,  //邮件 text 正文，限制 28K。注意：HtmlBody 和 TextBody 是针对不同类型的邮件内容，两者必须传其一。
            'toAddress'              => $this->to_email,  //目标地址，多个 email 地址可以用逗号分隔，最多 100 个地址（支持邮件组）。
//            'unSubscribeFilterLevel' => 'UnSubscribeFilterLevel', //过滤级别。参照退订功能生成链接和过滤机制文档。disabled: 不过滤，default: 采用默认策略，批量地址采用发信地址级别过滤，mailfrom: 发信地址级别过滤，mailfrom_domain: 发信域名级别过滤，edm_id: 账号级别过滤。https://help.aliyun.com/document_detail/2689048.html?spm=api-workbench.api_explorer.0.0.3c3d4cf62Sfbjt
//            'unSubscribeLinkType'    => 'UnSubscribeLinkType',  //生成的退订链接类型。参照退订功能生成链接和过滤机制文档。disabled: 不生成，default: 采用默认策略：对批量类型的发信地址发给特定域名时会生成退订链接，如带有关键字"gmail", "yahoo", "google", "aol.com", "hotmail", "outlook", "ymail.com"等，zh-cn: 生成，给将来埋点到内容准备，en-us: 生成，给将来埋点到内容准备。https://help.aliyun.com/document_detail/2689048.html?spm=api-workbench.api_explorer.0.0.3c3d4cf62Sfbjt
        ];
        $singleSendMailRequest = new SingleSendMailRequest($params);
        $runtime = new RuntimeOptions([]);
        try {
            // 复制代码运行请自行打印 API 的返回值
            $client->singleSendMailWithOptions($singleSendMailRequest, $runtime);
        }
        catch (Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }
            // 此处仅做打印展示，请谨慎对待异常处理，在工程项目中切勿直接忽略异常。
            // 错误 message
//            var_dump($error->message);
            // 诊断地址
//            var_dump($error->data["Recommend"]);
            if (!empty($GLOBALS['config']['weixin_robot']['xiaomei'])){
                $message = "使用阿里云发邮件失败了\r\n错误信息：{$error->message}\r\n诊断地址" . $error->data['Recommend'];
                tool_operate::I()->send_work_notice($message, $GLOBALS['config']['weixin_robot']['xiaomei']);
            }
            Utils::assertAsString($error->message);
        }
    }

    /**
     * @name 使用阿里云发邮件，老的，适用于PHP7.4.16
     * @return bool
     */
    public function send_by_aliyun_old()
    {
        if(empty($GLOBALS['config']['mailer']['aliyun']['access_key_id'])){
            write_applog('ERROR', '未设置阿里云邮件推送的access_key_id:'.json_encode($this->all_params()));
            return false;
        }
        if(empty($GLOBALS['config']['mailer']['aliyun']['access_key_secret'])){
            write_applog('ERROR', '未设置阿里云邮件推送的access_key_secret:'.json_encode($this->all_params()));
            return false;
        }
        if(empty($GLOBALS['config']['mailer']['aliyun']['from_name'])){
            write_applog('ERROR', '未设置阿里云邮件推送的from_name:'.json_encode($this->all_params()));
            return false;
        }
        if(empty($GLOBALS['config']['mailer']['aliyun']['from_email'])){
            write_applog('ERROR', '未设置阿里云邮件推送的from_email:'.json_encode($this->all_params()));
            return false;
        }
        $this->from_alias = $GLOBALS['config']['mailer']['aliyun']['from_name'];
        $this->from_email = $GLOBALS['config']['mailer']['aliyun']['from_email'];
        if(!$this->check_content()){
            write_applog('ERROR', '发送邮件失败,必填项缺失:'.json_encode($this->all_params()));
            return false;
        }
        include_once APP_PATH.'/vendor/aliyun-php-sdk-core/Config.php';
        //需要设置对应的region名称，如华东1（杭州）设为cn-hangzhou，新加坡Region设为ap-southeast-1，澳洲Region设为ap-southeast-2。
        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $GLOBALS['config']['mailer']['aliyun']['access_key_id'],
            $GLOBALS['config']['mailer']['aliyun']['access_key_secret']);
        //新加坡或澳洲region需要设置服务器地址，华东1（杭州）不需要设置。
        //$iClientProfile::addEndpoint("ap-southeast-1","ap-southeast-1","Dm","dm.ap-southeast-1.aliyuncs.com");
        //$iClientProfile::addEndpoint("ap-southeast-2","ap-southeast-2","Dm","dm.ap-southeast-2.aliyuncs.com");
        $client = new DefaultAcsClient($iClientProfile);
        $request = new Dm\SingleSendMailRequest();
        //新加坡或澳洲region需要设置SDK的版本，华东1（杭州）不需要设置。
        //$request->setVersion("2017-06-22");
        $request->setAccountName($this->from_email); //控制台创建的发信地址
        $request->setFromAlias($this->from_alias);   //发信人昵称
        $request->setAddressType(1);
        $request->setTagName($this->tag_name); //控制台创建的标签
        $request->setReplyToAddress("false");
        $request->setToAddress($this->to_email); //目标地址
        //可以给多个收件人发送邮件，收件人之间用逗号分开,若调用模板批量发信建议使用BatchSendMailRequest方式
        //$request->setToAddress("邮箱1,邮箱2");
        $request->setSubject($this->subject);   //邮件主题
        $request->setHtmlBody($this->html_body);  //邮件正文
        try {
            $response = $client->getAcsResponse($request);
        }
        catch (ClientException  $e) {
            write_applog('ERROR', '发送邮件失败，返回code:'.$e->getErrorCode().'，返回Message:'.$e->getErrorMessage());
            return false;
        }
        catch (ServerException  $e) {
            write_applog('ERROR', '发送邮件失败，返回code:'.$e->getErrorCode().'，返回Message:'.$e->getErrorMessage());
            return false;
        }
        return true;
    }

    /**
     * @name 使用PHPMailer发邮件
     * @return bool
     */
    public function send_by_phpmailer(){
        if(empty($GLOBALS['config']['mailer']['phpmailer']['host'])){
            write_applog('ERROR', '未设置PHPmailer的host:'.json_encode($this->all_params()));
            return false;
        }
        if(empty($GLOBALS['config']['mailer']['phpmailer']['mailer_type'])){
            write_applog('ERROR', '未设置PHPmailer的mailer_type:'.json_encode($this->all_params()));
            return false;
        }
        if(empty($GLOBALS['config']['mailer']['phpmailer']['username'])){
            write_applog('ERROR', '未设置PHPmailer的username:'.json_encode($this->all_params()));
            return false;
        }
        if(empty($GLOBALS['config']['mailer']['phpmailer']['password'])){
            write_applog('ERROR', '未设置PHPmailer的password:'.json_encode($this->all_params()));
            return false;
        }
        if(empty($GLOBALS['config']['mailer']['phpmailer']['port'])){
            write_applog('ERROR', '未设置PHPmailer的port:'.json_encode($this->all_params()));
            return false;
        }
        if(empty($GLOBALS['config']['mailer']['phpmailer']['SMTP_secure'])){
            write_applog('ERROR', '未设置PHPmailer的SMTP_secure:'.json_encode($this->all_params()));
            return false;
        }
        if(empty($GLOBALS['config']['mailer']['phpmailer']['from_name'])){
            write_applog('ERROR', '未设置阿里云邮件推送的from_name:'.json_encode($this->all_params()));
            return false;
        }

        if(empty($GLOBALS['config']['mailer']['phpmailer']['from_email'])){
            write_applog('ERROR', '未设置阿里云邮件推送的from_email:'.json_encode($this->all_params()));
            return false;
        }
        $this->from_alias = $GLOBALS['config']['mailer']['phpmailer']['from_name'];
        $this->from_email = $GLOBALS['config']['mailer']['phpmailer']['from_email'];
        if(!$this->check_content()){
            write_applog('ERROR', '发送邮件失败,必填项缺失:'.json_encode($this->all_params()));
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            //Server settings
            if(!empty($GLOBALS['config']['mailer']['phpmailer']['debug'])) {
                $mail->SMTPDebug = $GLOBALS['config']['mailer']['phpmailer']['debug']; // Enable verbose debug output
            }
            else{
                $mail->SMTPDebug = 0;
            }
            if(!empty($GLOBALS['config']['mailer']['phpmailer']['language'])) {
                $mail->setLanguage($GLOBALS['config']['mailer']['phpmailer']['language']);
            }
            else{
                $mail->setLanguage('zh_cn');
            }
            if(!empty($GLOBALS['config']['mailer']['phpmailer']['mailer_type'])) {
                switch(strtolower($GLOBALS['config']['mailer']['phpmailer']['mailer_type'])){
                    case 'mail':
                        $mail->isMail();
                        break;
                    case 'sendmail':
                        $mail->isSendmail();
                        break;
                    case 'qmail':
                        $mail->isQmail();
                        break;
                    case 'smtp':
                    default:
                        $mail->isSMTP();
                        break;
                }
            }
            $mail->Host       = $GLOBALS['config']['mailer']['phpmailer']['host'];  // Specify main and backup SMTP servers
            $mail->Username   = $GLOBALS['config']['mailer']['phpmailer']['username']; // SMTP username
            $mail->Password   = $GLOBALS['config']['mailer']['phpmailer']['password']; // SMTP password
            $mail->SMTPSecure = $GLOBALS['config']['mailer']['phpmailer']['SMTP_secure']; // Enable TLS encryption, `ssl` also accepted
            $mail->SMTPAuth = true;
            $mail->Port       = $GLOBALS['config']['mailer']['phpmailer']['port'];  // TCP port to connect to 465

            //Recipients
            $mail->setFrom($GLOBALS['config']['mailer']['phpmailer']['from_email'],
                $GLOBALS['config']['mailer']['phpmailer']['from_name']);

            if(!empty($GLOBALS['config']['mailer']['phpmailer']['reply_to_email'])) {
                $mail->addReplyTo($GLOBALS['config']['mailer']['phpmailer']['reply_to_email'],
                    $GLOBALS['config']['mailer']['phpmailer']['from_name']);
            }
//    $mail->addCC('cc@example.com');
//    $mail->addBCC('bcc@example.com');

            // Attachments
//    $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            // Content
            $body = '';
            if(trim($this->html_body)!==''){
                $mail->isHTML(true);                                  // Set email format to HTML
                $body = $this->html_body;
            }
            else if(trim($this->text_body)!==''){
                $body = $this->text_body;
            }
            $mail->Body    = $body;
//            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $mail->Subject = $this->subject;

            $emails = explode(',', $this->to_email);
            foreach($emails as $email) {
                $mail->addAddress($email, $this->to_alias);     // Add a recipient
                $mail->send();
            }
            return true;
        } catch (Exception $e) {
            write_applog('WARNING', '使用PHPmailer发送邮件失败,Mailer Error:'.$mail->ErrorInfo.', 邮件内容:'.json_encode($this->all_params()));
            return false;
        }

    }
}
