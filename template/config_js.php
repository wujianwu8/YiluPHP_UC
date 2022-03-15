/*
* 用于RSA加密的公匙
* 可以百度一下生成方法,将生成的private_key.pem文件中的内容拷贝到config.php的键rsa_private_key
* 再将对应的public_key.pem的内容去换行变成一行后拷贝到这个变量rsaPublicKey
* */
var rsaPublicKey = "<?php echo preg_replace('/\r/', '', preg_replace('/\n/', '', preg_replace('/\r\n/', '', empty($config['rsa_public_key'])?'':$config['rsa_public_key']))); ?>";
var rootDomain = "<?php echo $config['root_domain']; ?>";
var serverTime = <?php echo time(); ?>;
var main_lang = "<?php echo $config['main_lang']; ?>";
var support_lang = <?php echo json_encode(YiluPHP::I()->support_lang()); ?>;
var url_pre_lang = "<?php echo url_pre_lang(); ?>";