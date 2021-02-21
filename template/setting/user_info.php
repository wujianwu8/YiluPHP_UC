<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('menu_account_setting'),
];
?>

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
        <div class="col-sm-7">
            <?php echo $user_info['mobile']; ?>
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
                <a href="/setting/bind_email" class="btn btn-sm btn-outline-primary ajax_main_content">
                    <?php echo YiluPHP::I()->lang('set_login_email'); ?>
                </a>
            <?php }else{ ?>
                <?php echo $user_info['email']; ?>
                <a href="/setting/bind_email" class="ml-2 ajax_main_content">
                    <?php echo YiluPHP::I()->lang('change_login_email'); ?>
                </a>
            <?php } ?>
        </div>
    </div>
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
                <a href="/setting/unbind_wechat" class="ml-2 unbind_wechat">
                    <?php echo YiluPHP::I()->lang('unbind'); ?>
                </a>
            <?php } ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label><?php echo YiluPHP::I()->lang('bind_qq'); ?></label>
        </div>
        <div class="col-sm-7">
            <?php if(empty($user_info['QQ'])){ ?>
                <a href="/sign/qq_login/for_bind/1" class="btn btn-sm btn-outline-primary">
                    <?php echo YiluPHP::I()->lang('bind_now'); ?>
                </a>
                <div class="text-info">
                    <?php echo YiluPHP::I()->lang('after_binding_can_login_use_xxx', [
                        'field' => YiluPHP::I()->lang('user_identity_type_QQ')
                    ]); ?>
                </div>
            <?php }else{ ?>
                <?php echo YiluPHP::I()->lang('bind_already'); ?>
                <a href="/setting/unbind_qq" class="ml-2 unbind_qq">
                    <?php echo YiluPHP::I()->lang('unbind'); ?>
                </a>
            <?php } ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label><?php echo YiluPHP::I()->lang('bind_alipay'); ?></label>
        </div>
        <div class="col-sm-7">
            <?php if(empty($user_info['ALIPAY'])){ ?>
                <a href="/sign/alipay_login/for_bind/1" class="btn btn-sm btn-outline-primary">
                    <?php echo YiluPHP::I()->lang('bind_now'); ?>
                </a>
                <div class="text-info">
                    <?php echo YiluPHP::I()->lang('after_binding_can_login_use_xxx', [
                        'field' => YiluPHP::I()->lang('user_identity_type_ALIPAY')
                    ]); ?>
                </div>
            <?php }else{ ?>
                <?php echo YiluPHP::I()->lang('bind_already'); ?>
                <a href="/setting/unbind_alipay" class="ml-2 unbind_alipay">
                    <?php echo YiluPHP::I()->lang('unbind'); ?>
                </a>
            <?php } ?>
        </div>
    </div>


    <button class="btn btn-primary btn-lg btn-block" type="submit"><?php echo YiluPHP::I()->lang('save'); ?></button>
</form>
<div class="mb-5"></div>

<!--#include virtual="/include/js_config_js.shtml"-->
<!--#include virtual="/include/js_jsencrypt.shtml"-->
<!--#include virtual="/include/js_jweixin.shtml"-->
<script>
    //是否可以使用微信开放平台授权登录
    var haveWeixinOpen = <?php echo empty($config['oauth_plat']['wechat_open']['usable'])?'false':'true'; ?>;

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
                        , url: "/setting/save_info"
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