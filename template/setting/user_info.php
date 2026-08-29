<!--{use_layout layout/admin_main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('menu_account_setting'),
];
?>

<style>
    .mobile-setting-value{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap}
    .dialog.mobile-setting-modal .dialog-content{
        width:calc(100vw - 2rem);
        max-width:32.5rem;
        overflow:hidden;
        background:#fff;
    }
    .dialog.mobile-setting-modal .dialog-content-hd{
        padding:1.25rem 1.5rem .75rem;
        margin-bottom:0;
    }
    .dialog.mobile-setting-modal .dialog-content-title{
        font-family:inherit;
        font-weight:600;
    }
    .dialog.mobile-setting-modal .dialog-content-bd{
        margin:0;
        padding:1.25rem 1.5rem 1.5rem;
        text-align:left;
    }
    .dialog.mobile-setting-modal .dialog-content-ft .dialog-btn{
        font-family:inherit;
    }
    .mobile-setting-dialog{width:100%;min-width:0;text-align:left}
    .mobile-setting-dialog *{font-family:inherit!important}
    .mobile-setting-dialog .form-group{margin-bottom:1rem}
    .mobile-setting-dialog .form-group:last-child{margin-bottom:0}
    .mobile-setting-dialog label{display:block;margin-bottom:.4rem;color:#495057;font-weight:500}
    .mobile-setting-dialog .form-control,
    .mobile-setting-dialog .custom-select{
        display:block;
        width:100%;
        min-width:0;
        height:2.5rem;
        padding:.45rem .75rem;
        border:1px solid #ced4da;
        border-radius:.25rem;
        color:#212529;
        background-color:#fff;
        user-select:text;
        -webkit-user-select:text;
    }
    .mobile-setting-dialog .form-control:focus,
    .mobile-setting-dialog .custom-select:focus{
        border-color:#80bdff;
        box-shadow:0 0 0 .2rem rgba(0,123,255,.15);
    }
    .mobile-setting-dialog .mobile-code-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.625rem;align-items:stretch}
    .mobile-setting-dialog .mobile-code-row button{min-width:7.5rem;padding:.45rem .8rem;white-space:nowrap;border-radius:.25rem}
    .mobile-setting-dialog .alert{margin-bottom:1rem;padding:.75rem 1rem;border-radius:.25rem;line-height:1.55}
    @media(max-width:575.98px){
        .dialog.mobile-setting-modal .dialog-content{width:calc(100vw - 1.5rem)}
        .dialog.mobile-setting-modal .dialog-content-hd{padding:1rem 1rem .625rem}
        .dialog.mobile-setting-modal .dialog-content-bd{padding:1rem}
        .mobile-setting-dialog .form-group{margin-bottom:.8rem}
        .mobile-setting-dialog .mobile-code-row{grid-template-columns:minmax(0,1fr)}
        .mobile-setting-dialog .mobile-code-row button{width:100%;min-width:0}
    }
</style>

<h4 class="mb-3"><?php echo $head_info['title']; ?></h4>
<form class="needs-validation title_content" novalidate="" method="post" id="setting_user_info">
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="nickname"><?php echo YiluPHP::I()->lang('nickname'); ?></label>
        </div>
        <div class="col-sm-7">
            <input type="text" class="form-control" id="nickname" name="nickname" value="<?php echo $user_info['nickname']; ?>" required>
            <div class="invalid-feedback">
                <?php echo YiluPHP::I()->lang('please_input_xxx', ['field'=>strtolower(YiluPHP::I()->lang('nickname'))]); ?>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="gender"><?php echo YiluPHP::I()->lang('gender'); ?></label>
        </div>
        <div class="col-sm-7">
            <select class="custom-select d-block w-100" id="gender" name="gender" required>
                <option value="male" <?php echo $user_info['gender']=='male'?'selected' : ''; ?> >
                    <?php echo YiluPHP::I()->lang('gender_male'); ?>
                </option>
                <option value="female" <?php echo $user_info['gender']=='female'?'selected' : ''; ?> >
                    <?php echo YiluPHP::I()->lang('gender_female'); ?>
                </option>
            </select>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="username"><?php echo YiluPHP::I()->lang('birthday'); ?></label>
        </div>
        <div class="col-sm-7">
            <input type="date" class="form-control" id="birthday" name="birthday" value="<?php echo $user_info['birthday']; ?>" >
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="country"><?php echo YiluPHP::I()->lang('country'); ?></label>
        </div>
        <div class="col-sm-7">
            <select class="custom-select d-block w-100" id="country" name="country">
                <?php if(array_search($user_info['country'], $country_lang_keys)===false){ ?>
                <option value="<?php echo $user_info['country']; ?>">
                    <?php echo $user_info['country']; ?>
                </option>
                <?php } ?>
                <?php foreach ($country_lang_keys as $key){ ?>
                <option value="<?php echo $key; ?>" <?php echo $user_info['country']==$key?'selected':''; ?>>
                    <?php echo YiluPHP::I()->lang($key); ?>
                </option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="province"><?php echo YiluPHP::I()->lang('province'); ?></label>
        </div>
        <div class="col-sm-7">
            <input type="text" class="form-control" id="province" name="province" value="<?php echo $user_info['province']; ?>" >
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="city"><?php echo YiluPHP::I()->lang('city'); ?></label>
        </div>
        <div class="col-sm-7">
            <input type="text" class="form-control" id="city" name="city" value="<?php echo $user_info['city']; ?>" >
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label><?php echo YiluPHP::I()->lang('login_mobile'); ?></label>
        </div>
        <div class="col-sm-7 mobile-setting-value">
            <span id="current_login_mobile"><?php echo $user_info['mobile'] ?: '-'; ?></span>
            <a href="javascript:void(0);" id="btn_mobile_setting">
                <?php echo YiluPHP::I()->lang(empty($user_info['mobile']) ? 'bind_mobile' : 'change_mobile'); ?>
            </a>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="username"><?php echo YiluPHP::I()->lang('login_username'); ?></label>
        </div>
        <div class="col-sm-7">
            <?php if(empty($user_info['username'])){ ?>
                <input type="text" class="form-control" id="username" name="username" placeholder="<?php echo YiluPHP::I()->lang('cannot_be_modified_after_setting'); ?>">
                <div class="text-info">
                    <?php echo YiluPHP::I()->lang('username_rule_notice'); ?>
                </div>
            <?php }else{ ?>
                <?php echo $user_info['username']; ?>
            <?php } ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label><?php echo YiluPHP::I()->lang('login_email'); ?></label>
        </div>
        <div class="col-sm-7">
            <?php if(empty($user_info['email'])){ ?>
                <a href="<?php echo url_pre_lang(); ?>/setting/bind_email" class="btn btn-sm btn-outline-primary ajax_main_content">
                    <?php echo YiluPHP::I()->lang('set_login_email'); ?>
                </a>
            <?php }else{ ?>
                <?php echo $user_info['email']; ?>
                <a href="<?php echo url_pre_lang(); ?>/setting/bind_email" class="ml-2 ajax_main_content">
                    <?php echo YiluPHP::I()->lang('change_login_email'); ?>
                </a>
            <?php } ?>
        </div>
    </div>

    <?php if(!empty($config['oauth_plat']['wechat']['usable']) || !empty($config['oauth_plat']['wechat_open']['usable'])){ ?>
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label><?php echo YiluPHP::I()->lang('bind_wechat'); ?></label>
        </div>
        <div class="col-sm-7">
            <?php if(empty($user_info['WX'])){ ?>
                <a href="javascript:weixinLogin(1);" class="btn btn-sm btn-outline-primary">
                    <?php echo YiluPHP::I()->lang('bind_now'); ?>
                </a>
                <div class="text-info">
                    <?php echo YiluPHP::I()->lang('after_binding_can_login_use_xxx', [
                            'field' => YiluPHP::I()->lang('user_identity_type_WX')
                    ]); ?>
                </div>
            <?php }else{ ?>
                <?php echo YiluPHP::I()->lang('bind_already'); ?>
                <a href="<?php echo url_pre_lang(); ?>/setting/unbind_wechat" class="ml-2 unbind_wechat">
                    <?php echo YiluPHP::I()->lang('unbind'); ?>
                </a>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <?php if(!empty($config['oauth_plat']['qq']['usable'])){ ?>
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label><?php echo YiluPHP::I()->lang('bind_qq'); ?></label>
        </div>
        <div class="col-sm-7">
            <?php if(empty($user_info['QQ'])){ ?>
                <a href="<?php echo url_pre_lang(); ?>/sign/qq_login/for_bind/1" class="btn btn-sm btn-outline-primary">
                    <?php echo YiluPHP::I()->lang('bind_now'); ?>
                </a>
                <div class="text-info">
                    <?php echo YiluPHP::I()->lang('after_binding_can_login_use_xxx', [
                        'field' => YiluPHP::I()->lang('user_identity_type_QQ')
                    ]); ?>
                </div>
            <?php }else{ ?>
                <?php echo YiluPHP::I()->lang('bind_already'); ?>
                <a href="<?php echo url_pre_lang(); ?>/setting/unbind_qq" class="ml-2 unbind_qq">
                    <?php echo YiluPHP::I()->lang('unbind'); ?>
                </a>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <?php if(!empty($config['oauth_plat']['alipay']['usable'])){ ?>
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label><?php echo YiluPHP::I()->lang('bind_alipay'); ?></label>
        </div>
        <div class="col-sm-7">
            <?php if(empty($user_info['ALIPAY'])){ ?>
                <a href="<?php echo url_pre_lang(); ?>/sign/alipay_login/for_bind/1" class="btn btn-sm btn-outline-primary">
                    <?php echo YiluPHP::I()->lang('bind_now'); ?>
                </a>
                <div class="text-info">
                    <?php echo YiluPHP::I()->lang('after_binding_can_login_use_xxx', [
                        'field' => YiluPHP::I()->lang('user_identity_type_ALIPAY')
                    ]); ?>
                </div>
            <?php }else{ ?>
                <?php echo YiluPHP::I()->lang('bind_already'); ?>
                <a href="<?php echo url_pre_lang(); ?>/setting/unbind_alipay" class="ml-2 unbind_alipay">
                    <?php echo YiluPHP::I()->lang('unbind'); ?>
                </a>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <button class="btn btn-primary btn-lg btn-block" type="submit"><?php echo YiluPHP::I()->lang('save'); ?></button>
</form>
<div class="mb-5"></div>

<script src="<?php echo url_pre_lang(); ?>/config_js" type="text/javascript"></script>
<?php echo load_static('/include/js_jsencrypt.shtml'); ?>
<?php echo load_static('/include/js_jweixin.shtml'); ?>
<script>
    //是否可以使用微信开放平台授权登录
    var haveWeixinOpen = <?php echo empty($config['oauth_plat']['wechat_open']['usable'])?'false':'true'; ?>;
    var mobileSettingHasPassword = <?php echo $has_login_password ? 'true' : 'false'; ?>;
    var mobileSettingHasMobile = <?php echo empty($user_info['mobile']) ? 'false' : 'true'; ?>;
    var mobileSettingAreas = <?php echo json_encode($area_list, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;

    function mobileSettingNotice(message, dialogClass) {
        $(document).dialog({
            type: "notice",
            position: "bottom",
            dialogClass: dialogClass || "dialog_warn",
            infoText: message,
            autoClose: 3000,
            overlayShow: false
        });
    }

    function mobileSettingAreaOptions() {
        var html = '';
        for (var i = 0; i < mobileSettingAreas.length; i++) {
            var item = mobileSettingAreas[i];
            html += '<option value="'+item.code_number+'">(+'+item.code_number+') '+item.name+'</option>';
        }
        return html;
    }

    function openMobileSettingDialog() {
        var passwordHtml = mobileSettingHasPassword
            ? '<div class="form-group"><label for="mobile_login_password">'+getLang('enter_login_password')+'</label><input type="password" id="mobile_login_password" class="form-control" autocomplete="current-password" placeholder="'+getLang('enter_login_password_here')+'"></div>'
            : '<div class="alert alert-info">'+getLang('current_account_has_no_password')+'</div>'+
              '<div class="form-group"><label for="mobile_new_password">'+getLang('set_login_password')+'</label><input type="password" id="mobile_new_password" class="form-control" autocomplete="new-password" placeholder="'+getLang('password_rule_placeholder')+'"></div>'+
              '<div class="form-group"><label for="mobile_confirm_password">'+getLang('confirm_new_password')+'</label><input type="password" id="mobile_confirm_password" class="form-control" autocomplete="new-password" placeholder="'+getLang('confirm_login_password_please')+'"></div>';
        var content = '<div class="mobile-setting-dialog">'+
            '<div class="form-group"><label for="mobile_area_code">'+getLang('country_region')+'</label><select id="mobile_area_code" class="custom-select">'+mobileSettingAreaOptions()+'</select></div>'+
            '<div class="form-group"><label for="mobile_number">'+getLang('new_mobile')+'</label><input type="tel" inputmode="numeric" id="mobile_number" class="form-control" maxlength="11" placeholder="'+getLang('please_input_mobile_number')+'"></div>'+
            '<div class="form-group"><label for="mobile_verify_code">'+getLang('sms_verify_code')+'</label><div class="mobile-code-row"><input type="text" inputmode="numeric" id="mobile_verify_code" class="form-control" maxlength="6" placeholder="'+getLang('please_input_verify_code')+'"><button type="button" class="btn btn-secondary" id="btn_send_mobile_code">'+getLang('send_verify_code')+'</button></div></div>'+
            passwordHtml+'</div>';

        var inputDialog = $(document).dialog({
            type: 'confirm',
            dialogClass: 'mobile-setting-modal',
            titleText: getLang(mobileSettingHasMobile ? 'change_mobile' : 'bind_mobile'),
            content: content,
            contentScroll: false,
            buttonTextConfirm: getLang('confirm_save'),
            onClickConfirmBtn: function () {
                var mobile = $.trim($('#mobile_number').val());
                var code = $.trim($('#mobile_verify_code').val());
                if (!/^\d{6,11}$/.test(mobile)) {
                    mobileSettingNotice(getLang('wrong_mobile_number'));
                    return false;
                }
                if (!/^\d{4,6}$/.test(code)) {
                    mobileSettingNotice(getLang('verify_code_error'));
                    return false;
                }
                var params = {dtype:'json', area_code:$('#mobile_area_code').val(), mobile:mobile, verify_code:code};
                var encryptFields = ['mobile'];
                if (mobileSettingHasPassword) {
                    params.password = $('#mobile_login_password').val();
                    if (!params.password) {
                        mobileSettingNotice(getLang('enter_login_password'));
                        return false;
                    }
                    encryptFields.push('password');
                } else {
                    params.new_password = $('#mobile_new_password').val();
                    params.confirm_password = $('#mobile_confirm_password').val();
                    if (!is_password(params.new_password)) {
                        mobileSettingNotice(getLang('password_too_simple'));
                        return false;
                    }
                    if (params.new_password !== params.confirm_password) {
                        mobileSettingNotice(getLang('re_input_password_error'));
                        return false;
                    }
                    encryptFields.push('new_password', 'confirm_password');
                }
                params = rsaEncryptData(params, encryptFields);
                var toast = loading();
                $.post(url_pre_lang+'/setting/save_mobile', params, function (data) {
                    toast.close();
                    if (data.code === 0) {
                        inputDialog.close();
                        $(document).dialog({titleShow:false, content:data.msg, contentScroll:false, onClickConfirmBtn:function(){reloadPage();}});
                    } else {
                        mobileSettingNotice(data.msg);
                    }
                }, 'json').fail(function () {
                    toast.close();
                    mobileSettingNotice(getLang('network_error_retry'), 'dialog_red');
                });
                return false;
            }
        });

        $('#btn_send_mobile_code').off('click').on('click', function () {
            var button = $(this);
            var mobile = $.trim($('#mobile_number').val());
            if (!/^\d{6,11}$/.test(mobile)) {
                mobileSettingNotice(getLang('wrong_mobile_number'));
                return;
            }
            var params = {dtype:'json', area_code:$('#mobile_area_code').val(), mobile:mobile, use_for:'bind_account'};
            params = rsaEncryptData(params, ['mobile']);
            button.prop('disabled', true).addClass('btn_loading');
            $.post(url_pre_lang+'/send_sms_code', params, function (data) {
                button.removeClass('btn_loading');
                if (data.code !== 0) {
                    button.prop('disabled', false);
                    mobileSettingNotice(data.msg);
                    return;
                }
                mobileSettingNotice(getLang('verify_code_sent'), 'dialog_blue');
                var left = 30;
                button.text(left);
                var timer = setInterval(function () {
                    left--;
                    button.text(left);
                    if (left <= 0) {
                        clearInterval(timer);
                        button.text(getLang('send_verify_code')).prop('disabled', false);
                    }
                }, 1000);
            }, 'json').fail(function () {
                button.removeClass('btn_loading').prop('disabled', false);
                mobileSettingNotice(getLang('network_error_retry'), 'dialog_red');
            });
        });
    }

    $('#btn_mobile_setting').on('click', openMobileSettingDialog);

    (function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');

        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                event.stopPropagation();

                var params = {
                    dtype:"json"
                };
                var inputs = $(form).serializeArray();
                for(var index in inputs){
                    var item = inputs[index];
                    if (item.name == "username"){
                        item.value = $.trim(item.value);
                        if (item.value != ""){
                            //检查用户名的有效性
                            if (!item.value.match(/^[\w\d_\-\.]{3,50}$/) || item.value.match(/^[\d]+$/)){
                                $(document).dialog({
                                    content: getLang("username_rule_notice")
                                    , overlayShow: false
                                });
                                return false;
                            }
                        }
                    }
                    if (item.name == "nickname") {
                        nickname = $.trim(item.value);
                        if (nickname == "") {
                            $(document).dialog({
                                type: "notice"
                                , position: "bottom"
                                , dialogClass: "dialog_warn"
                                , infoText: getLang("please_input_xxx", {field:getLang("nickname")})
                                , autoClose: 3000
                                , overlayShow: false
                            });
                            $("#nickname").focus();
                            break;
                        }
                    }
                    params[item.name] = item.value;
                }

                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return false;
                }
                var toast = loading();
                $.ajax({
                        type: 'post'
                        , dataType: 'json'
                        , url: "<?php echo url_pre_lang(); ?>/setting/save_info"
                        , data: params
                        , success: function (data, textStatus, jqXHR) {
                            toast.close();
                            if (data.code == 0) {
                                toast.dialog({
                                    overlayClose: true
                                    , titleShow: false
                                    , content: getLang("save_successfully")
                                });
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
                            toast.close();
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

            }, false);
        });

        $("#setting_user_info").bind("click", function (e) {
            var obj = null;
            if(e.target.tagName.toLocaleUpperCase() == "A"){
                obj = $(e.target);
            }
            else if(e.target.parentNode.tagName.toLocaleUpperCase() == "A"){
                obj = $(e.target.parentNode);
            }

            if(obj!==null) {
                if (obj.hasClass("unbind_qq") || obj.hasClass("unbind_wechat") || obj.hasClass("unbind_alipay")) {
                    e.preventDefault();
                    title = obj.attr("title");
                    url = obj.attr("href");
                    var inputDialog = $(document).dialog({
                        type: "confirm",
                        titleText: title,
                        content: '<div>'+getLang("enter_login_password")+'</div><div><input type="password" id="password" class="form-control mt-2" placeholder="'+getLang("enter_login_password_here")+'"></div>',
                        contentScroll: false,
                        buttonTextConfirm: getLang("unbind_now"),
                        onClickConfirmBtn: function () {
                            password = $("#password").val();
                            if (!is_password(password)) {
                                $(document).dialog({
                                    type: "notice"
                                    , position: "bottom"
                                    , dialogClass: "dialog_warn"
                                    , infoText: getLang("login_password_error")
                                    , autoClose: 3000
                                    , overlayShow: false
                                });
                                return false;
                            }
                            params = {
                                dtype: "json",
                                password: $("#password").val()
                            };
                            params = rsaEncryptData(params, ["password"]);
                            var toast = loading();
                            $.ajax({
                                    type: 'post'
                                    , dataType: 'json'
                                    , url: url
                                    , data: params
                                    , success: function (data, textStatus, jqXHR) {
                                        toast.close();
                                        if (data.code == 0) {
                                            inputDialog.close();
                                            $(document).dialog({
                                                titleShow: false
                                                , content: data.msg
                                                , contentScroll: false
                                                , onClickConfirmBtn: function () {
                                                    reloadPage();
                                                }
                                            });

                                        } else {
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
                                        toast.close();
                                        $(document).dialog({
                                            type: "notice"
                                            , position: "bottom"
                                            , dialogClass: "dialog_red"
                                            , infoText: textStatus
                                            , autoClose: 3000
                                            , overlayShow: false
                                        });
                                    }
                                }
                            );
                            return false;
                        }
                    });
                }
            }
        })
    })();
</script>
