<!--{use_layout layout/admin_main}-->
<?php
$head_info = [
    'title' => '绑定登录邮箱',
];
?>

<script src="<?php echo url_pre_lang(); ?>/config_js" type="text/javascript"></script>
<?php echo load_static('/include/js_jsencrypt.shtml'); ?>
<h4 class="mb-3"><?php echo $head_info['title']; ?></h4>
<form class="needs-validation title_content" novalidate="" method="post">
    <div class="row mb-3">
        <div class="col-md-3 title">
            <label for="email">邮箱地址</label>
        </div>
        <div class="col-md-7">
            <div class="input-group">
                <input type="text" class="form-control" id="email" name="email" placeholder="输入您的邮箱地址" value="<?php echo $email; ?>" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-secondary" id="btn_send_email_code">发送验证码</button>
                </div>
                <div class="invalid-feedback">
                    必须设置一个邮箱地址
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-3 title">
            <label for="verify_code">验证码</label>
        </div>
        <div class="col-md-7">
            <input type="text" class="form-control" id="verify_code" name="verify_code" placeholder="邮箱中收到的验证码" required>
            <div class="invalid-feedback">
                请输入您的邮箱中收到的验证码
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-3 title">
            <label for="password">验证登录密码</label>
        </div>
        <div class="col-md-7">
            <input type="password" class="form-control" id="password" name="password" placeholder="输入您的登录密码" required>
            <div class="invalid-feedback">
                请输入您的登录密码
            </div>
        </div>
    </div>

    <button class="btn btn-primary btn-lg btn-block" type="submit">保存</button>
</form>
<div class="mb-5"></div>
<script>
    (function() {

        $("#btn_send_email_code").click(function(){
            var email = $.trim($("#email").val());
            if(email==""){
                $(document).dialog({
                    type: "notice"
                    ,position: "bottom"
                    ,dialogClass:"dialog_warn"
                    ,infoText: "请填写邮箱地址"
                    ,autoClose: 3000
                    ,overlayShow: false
                });
                return false;
            }
            if(!is_email(email)){
                $(document).dialog({
                    type: "notice"
                    ,position: "bottom"
                    ,dialogClass:"dialog_warn"
                    ,infoText: "请填写正确的邮箱地址"
                    ,autoClose: 3000
                    ,overlayShow: false
                });
                return false;
            }

            $("#btn_send_email_code").addClass("btn_loading").attr("disabled", true);
            params = {
                email:email,
                use_for:'bind_account',
                dtype:"json"
            };
            params = rsaEncryptData(params, ["email"]);
            $.post(
                url_pre_lang+"/send_email_code",
                params,
                function(data,status){
                    $("#btn_send_email_code").removeClass("btn_loading");
                    console.log(data);
                    if(data.code==0){
                        $(document).dialog({
                            type: "notice"
                            ,position: "bottom"
                            ,dialogClass:"dialog_blue"
                            ,infoText: "验证码已发送"
                            ,autoClose: 30000
                            ,overlayShow: false
                        });
                        var leftTime = 30;
                        $("#btn_send_email_code").text(leftTime);
                        var time = setInterval(function(){
                            leftTime--;
                            $("#btn_send_email_code").text(leftTime);
                            if(leftTime<=0){
                                clearInterval(time);
                                $("#btn_send_email_code").text("Send Code").removeAttr("disabled");
                            }
                        },1000);
                    }
                    else {
                        $(document).dialog({
                            type: "notice"
                            ,position: "bottom"
                            ,dialogClass:"dialog_warn"
                            ,infoText: data.msg
                            ,autoClose: 3000
                            ,overlayShow: false
                        });
                        $("#btn_send_email_code").removeAttr("disabled");
                    }
                },
                "json"
            );
        });

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
                    if (item.name == "email"){
                        item.value = $.trim(item.value);
                        if (item.value != ""){
                            //检查用户名的有效性
                            if (!is_email(item.value)){
                                $(document).dialog({
                                    type: "notice"
                                    ,position: "bottom"
                                    ,dialogClass:"dialog_warn"
                                    ,infoText: "请填写正确的邮箱地址"
                                    ,autoClose: 3000
                                    ,overlayShow: false
                                });
                                return false;
                            }
                        }
                    }
                    params[item.name] = item.value;
                }

                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return false;
                }
                params = rsaEncryptData(params, ["email","password"]);
                var toast = loading();
                $.ajax({
                        type: 'post'
                        , dataType: 'json'
                        , url: "<?php echo url_pre_lang(); ?>/setting/save_email"
                        , data: params
                        , success: function (data, textStatus, jqXHR) {
                            toast.close();
                            if (data.code == 0) {
                                toast.dialog({
                                    overlayClose: true
                                    , titleShow: false
                                    , content: "绑定成功"
                                    , onClickConfirmBtn: function(){
                                        $.getMainHtml("<?php echo url_pre_lang(); ?>/setting/user_info", {with_layout:0,dtype:'json'});
                                    }
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
    })();
</script>