<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('menu_change_password'),
];
?>

<!--#include virtual="/include/js_config_js.shtml"-->
<!--#include virtual="/include/js_jsencrypt.shtml"-->
<h4 class="mb-3"><?php echo $head_info['title']; ?></h4>
<form class="needs-validation title_content" novalidate="" method="post">
    <div class="row mb-3">
        <div class="col-sm-3 title">
            <label for="email"><?php echo YiluPHP::I()->lang('new_password'); ?></label>
        </div>
        <div class="col-sm-7">
            <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo YiluPHP::I()->lang('new_password'); ?>" required>
            <div class="invalid-feedback">
                <?php echo YiluPHP::I()->lang('please_input_xxx', ['field'=>strtolower(YiluPHP::I()->lang('new_password'))]); ?>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-sm-3 title">
            <label for="confirm_password"><?php echo YiluPHP::I()->lang('confirm_new_password'); ?></label>
        </div>
        <div class="col-sm-7">
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="<?php echo YiluPHP::I()->lang('confirm_new_password'); ?>" required>
            <div class="invalid-feedback">
                <?php echo YiluPHP::I()->lang('confirm_new_password_please'); ?>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-sm-3 title">
            <label for="password"><?php echo YiluPHP::I()->lang('current_password'); ?></label>
        </div>
        <div class="col-sm-7">
            <input type="password" class="form-control" id="current_password" name="current_password" placeholder="<?php echo YiluPHP::I()->lang('current_password'); ?>" required>
            <div class="invalid-feedback">
                <?php echo YiluPHP::I()->lang('please_input_xxx', ['field'=>strtolower(YiluPHP::I()->lang('current_password'))]); ?>
            </div>
        </div>
    </div>

    <button class="btn btn-primary btn-lg btn-block" type="submit"><?php echo YiluPHP::I()->lang('save'); ?></button>
</form>
<div class="mb-5"></div>
<script>
    (function() {

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');

        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                event.stopPropagation();

                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return false;
                }

                var params = {
                    dtype:"json"
                };
                var inputs = $(form).serializeArray();
                for(var index in inputs){
                    var item = inputs[index];
                    switch (item.name){
                        case "password":
                            if(!is_password(item.value)){
                                $(document).dialog({
                                    type: "notice"
                                    ,position: "bottom"
                                    ,dialogClass:"dialog_warn"
                                    ,infoText: getLang("password_too_simple")
                                    ,autoClose: 5000
                                    ,overlayShow: false
                                });
                                return false;
                            }
                            break;
                        case "confirm_password":
                            if(item.value!=params.password){
                                $(document).dialog({
                                    type: "notice"
                                    ,position: "bottom"
                                    ,dialogClass:"dialog_warn"
                                    ,infoText: getLang("re_input_password_error")
                                    ,autoClose: 3000
                                    ,overlayShow: false
                                });
                                return false;
                            }
                            break;
                        case "current_password":
                            if(!is_password(item.value)){
                                $(document).dialog({
                                    type: "notice"
                                    ,position: "bottom"
                                    ,dialogClass:"dialog_warn"
                                    ,infoText: getLang("current_password_error")
                                    ,autoClose: 3000
                                    ,overlayShow: false
                                });
                                return false;
                            }
                            break;
                        default:
                            break;
                    }
                    if(item.name != "confirm_password"){
                        params[item.name] = item.value;
                    }
                }
                params = rsaEncryptData(params, ["password","current_password"]);
                var toast = loading();
                $.ajax({
                        type: 'post'
                        , dataType: 'json'
                        , url: "/setting/save_password"
                        , data: params
                        , success: function (data, textStatus, jqXHR) {
                            toast.close();
                            if (data.code == 0) {
                                toast.dialog({
                                    overlayClose: true
                                    , titleShow: false
                                    , content: getLang("modification_succeeded_login_again")
                                    , onClickConfirmBtn: function(){
                                        document.location.href="/";
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