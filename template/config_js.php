/*
* 用于RSA加密的公匙
* 可以百度一下生成方法,将生成的private_key.pem文件中的内容拷贝到config.php的键rsa_private_key
* 再将对应的public_key.pem的内容去换行变成一行后拷贝到这个变量rsaPublicKey
* */
var rsaPublicKey = "<?php echo preg_replace('/\r/', '', preg_replace('/\n/', '', preg_replace('/\r\n/', '', empty($config['rsa_public_key'])?'':$config['rsa_public_key']))); ?>";
var root_domain = "<?php echo $config['root_domain']; ?>";