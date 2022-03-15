<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?php echo YiluPHP::I()->lang('bind_account'); ?></title>
    <?php echo load_static('/include/css_bootstrap.shtml'); ?>
    <?php echo load_static('/include/css_dialog.shtml'); ?>
    <?php echo load_static('/include/css_base.shtml'); ?>
    <?php echo load_static('/include/css_sign.shtml'); ?>
    <?php echo load_static('/include/js_jquery.shtml'); ?>
    <?php echo load_static('/include/js_jquery_cookie.shtml'); ?>
    <script src="<?php echo url_pre_lang(); ?>/config_js" type="text/javascript"></script>
    <?php echo load_static('/include/js_jsencrypt.shtml'); ?>
    <?php echo load_static('/include/js_dialog_diy.shtml'); ?>
    <?php echo load_static('/include/js_base.shtml'); ?>
    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?802be9112dbcdf29bc10f0eabed49dca";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>
</head>

<body>
<div class="language_handle">
    <a href="javascript:changeLanguage('<?php echo YiluPHP::I()->current_lang()=='cn' ?'selected':'cn'; ?>');" class="<?php echo YiluPHP::I()->current_lang()=='cn' ?'selected':''; ?>" >简体中文</a>
    <a href="javascript:changeLanguage('<?php echo YiluPHP::I()->current_lang()=='en' ?'selected':'en'; ?>');" class="<?php echo YiluPHP::I()->current_lang()=='en' ?'selected':''; ?>" >English</a>
</div>

<div class="form-signin">
    <div class="user-info">
        <img src="<?php echo $avatar; ?>">
        <div><?php echo $nickname; ?>
            <strong>[<?php echo $from_plat; ?>]</strong>
        </div>
    </div>

<!--   class="bind-new"  -->
    <div id="bind_widget" class="bind-new">
        <div class="form-bind-title">
            <span class="existing-account"><?php echo YiluPHP::I()->lang('bind_existing_account'); ?></span>
            <span class="new-account"><?php echo YiluPHP::I()->lang('bind_new_account'); ?></span>
        </div>
        <form class="new-account-form" name="new-account-form" onsubmit="return submitRegisterForm(this)">
            <input type="hidden" name="is_bind" value="1">
            <div><?php echo YiluPHP::I()->lang('your_mobile_located_in'); ?></div>
            <div class="mb-3">
                <select class="custom-select d-block w-100" name="area_code" id="area_code_2">
                    <?php foreach ($area_list as $item): ?>
                        <option value="<?php echo $item['code_number']; ?>" <?php if(!empty($item['recommend'])): ?>style="color: blue"<?php endif; ?> >
                            (+<?php echo $item['code_number']; echo strlen($item['code_number'])<2 ? '&nbsp;&nbsp;':''; ?>）<?php echo $item['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <input type="number" name="mobile" id="mobile" class="form-control" placeholder="<?php echo YiluPHP::I()->lang('mobile_number'); ?>" value="" required autofocus>
            </div>

            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="<?php echo YiluPHP::I()->lang('please_set_your_password'); ?>" value="" required>
            </div>

            <div class="mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="<?php echo YiluPHP::I()->lang('confirm_new_password'); ?>" value="" required>
            </div>

            <div class="mb-3">
                <div class="input-group">
                    <input type="number" class="form-control" name="verify_code" value="" placeholder="<?php echo YiluPHP::I()->lang('verify_code'); ?>" maxlength="6" minlength="4" oninput="if(value.length>6) value=value.slice(0,6)" required>
                    <div class="input-group-append">
                        <button type="button" id="btn_send_sms_code" class="btn btn-secondary" use_for="bind_account" style="width: 130px;"><?php echo YiluPHP::I()->lang('send_verify_code'); ?></button>
                    </div>
                </div>
            </div>
            <button class="btn btn-lg btn-primary btn-block" type="submit" id="registerButton"><?php echo YiluPHP::I()->lang('sign_up_and_bind'); ?></button>
        </form>
        <form class="existing-account-form" name="existing-account-form" onsubmit="return submitLoginForm(this)">
            <input type="hidden" name="is_bind" value="1">
            <div class="mb-3">
                <input type="text" id="identity" name="identity" class="form-control" placeholder="<?php echo YiluPHP::I()->lang('login_name_placeholder'); ?>" value="" required autofocus onchange="checkIdentityType()" />
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="<?php echo YiluPHP::I()->lang('login_password'); ?>" value="" required oninput="checkIdentityType()">
            </div>
<!--    show-area-select    -->
            <div class="mb-3" id="existing-area-select">
                <div><?php echo YiluPHP::I()->lang('your_mobile_located_in'); ?></div>
                <select class="custom-select d-block w-100" name="area_code" id="area_code_1">
                    <?php foreach ($area_list as $item): ?>
                        <option value="<?php echo $item['code_number']; ?>" <?php if(!empty($item['recommend'])): ?>style="color: blue"<?php endif; ?> >
                            (+<?php echo $item['code_number']; echo strlen($item['code_number'])<2 ? '&nbsp;&nbsp;':''; ?>）<?php echo $item['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <button class="btn btn-lg btn-primary btn-block" type="submit" id="loginBind"><?php echo YiluPHP::I()->lang('sign_in_and_bind'); ?></button>
            </div>
            <div class="checkbox" style="overflow: hidden;zoom:1;margin-top: 20px;margin-bottom: 30px;">
                <a href="<?php echo url_pre_lang(); ?>/find_password" style="float: left;"><?php echo YiluPHP::I()->lang('forgot_password'); ?></a>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    function submitLoginForm(_this){
        var params = {
            dtype:"json"
        };
        var inputs = $(_this).serializeArray();
//        console.log(inputs);
        for(var index in inputs){
            item = inputs[index];
//            console.log(index, item);
            params[item.name] = item.value;
            switch (item.name){
                case "identity":
                    if($.trim(item.value)==""){
                        $(document).dialog({
                            type: "notice"
                            ,position: "bottom"
                            ,dialogClass:"dialog_warn"
                            ,infoText: "请填写您的登录账户"
                            ,autoClose: 3000
                            ,overlayShow: false
                        });
                        return false;
                    }
                    if(!item.value.match(/^[\w\-_\.@~!#$%^&*\(\)<>]{2,100}$/)){
                        $(document).dialog({
                            type: "notice"
                            ,position: "bottom"
                            ,dialogClass:"dialog_warn"
                            ,infoText: "登录账户不存在"
                            ,autoClose: 3000
                            ,overlayShow: false
                        });
                        return false;
                    }
                    if(item.value.search("@")>0 && !item.value.match(/^[\w\-_\.]+@[\w\-_]+\.[\w\-_\.]{2,30}$/)){
                        $(document).dialog({
                            type: "notice"
                            ,position: "bottom"
                            ,dialogClass:"dialog_warn"
                            ,infoText: "登录账户不存在"
                            ,autoClose: 3000
                            ,overlayShow: false
                        });
                        return false;
                    }
                    break;
                case "password":
                    if($.trim(item.value)==""){
                        $(document).dialog({
                            type: "notice"
                            ,position: "bottom"
                            ,dialogClass:"dialog_warn"
                            ,infoText: "请输入密码"
                            ,autoClose: 3000
                            ,overlayShow: false
                        });
                        return false;
                    }
                    if(!item.value.match(/^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*)(?=.*[\.\$!#@_-].*).{6,20}$/)){
                        $(document).dialog({
                            type: "notice"
                            ,position: "bottom"
                            ,dialogClass:"dialog_warn"
                            ,infoText: "密码错误"
                            ,autoClose: 3000
                            ,overlayShow: false
                        });
                        return false;
                    }
                    break;
                default:
                    break;
            }
        }
        // console.log(params);
        params = rsaEncryptData(params, ["identity","password"]);
//      console.log(params);
        $("#loginBind").addClass("btn_loading").attr("disabled", true);
        $.ajax({
                type: 'post'
                , dataType: 'json'
                , url: "<?php echo url_pre_lang(); ?>/sign/login"
                , data: params
                , success: function (data, textStatus, jqXHR) {
                    $("#loginBind").removeClass("btn_loading").removeAttr("disabled", true);
                    console.log(data.code, textStatus, jqXHR);
                    if (data.code == 0) {
                        $(document).dialog({
                            type: "notice"
                            , position: "bottom"
                            , dialogClass: "dialog_blue"
                            , infoText: "登录并绑定账号成功!"
                            , autoClose: 3000
                            , overlayShow: false
                        });
                        document.location.href = data.data.redirect_uri;
                    }
                    else {
                        $(document).dialog({
                            type: "notice"
                            , position: "bottom"
                            , dialogClass: "dialog_warn"
                            , infoText: data.msg
                            , autoClose: 3000
                            , overlayShow: false
                        });
                    }
                }
                , error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.log(XMLHttpRequest, textStatus, errorThrown);
                    $("#loginBind").removeClass("btn_loading").removeAttr("disabled", true);
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_red"
                        ,infoText: textStatus
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                }
            }
        );
        return false;
    }

    $(function(){

        $(".existing-account").click(function(){
            $("#bind_widget").removeClass("bind-new");
            $("#bind_widget").removeClass("bind-new-animation");
            $("#bind_widget").addClass("bind-existing-animation");
        });
        $(".new-account").click(function(){
            $("#bind_widget").addClass("bind-new");
            $("#bind_widget").removeClass("bind-existing-animation");
            $("#bind_widget").addClass("bind-new-animation");
        });
        checkIdentityType();
    });

</script>
<?php echo load_static('/include/js_no_logged_in.shtml'); ?>
<script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? "https://" : "http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1278278388'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s4.cnzz.com/z_stat.php%3Fid%3D1278278388' type='text/javascript'%3E%3C/script%3E"));</script>
</body>
</html>