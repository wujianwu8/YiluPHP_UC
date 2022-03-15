<!--{use_layout layout/admin_main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('add_user'),
];
?>

<h4 class="mb-3"><?php echo YiluPHP::I()->lang('add_user'); ?></h4>
<form class="needs-validation title_content" novalidate="" method="post">
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="nickname"><?php echo YiluPHP::I()->lang('nickname'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="nickname" name="nickname" required="1" minlength="1" maxlength="50">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="area_code"><?php echo YiluPHP::I()->lang('your_mobile_located_in'); ?></label>
        </div>
        <div class="col-sm-9">
            <select class="custom-select d-block w-100" id="area_code" name="area_code">
                <?php foreach ($area_list as $item): ?>
                    <option value="<?php echo $item['code_number']; ?>" <?php if(!empty($item['recommend'])): ?>style="color: blue"<?php endif; ?> >
                        (+<?php echo $item['code_number']; echo strlen($item['code_number'])<2 ? '&nbsp;&nbsp;':''; ?>）<?php echo $item['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="mobile"><?php echo YiluPHP::I()->lang('login_mobile'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="number" class="form-control" id="mobile" name="mobile" required="1" minlength="11" maxlength="11">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="password"><?php echo YiluPHP::I()->lang('login_password'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="password" name="password" required="1" minlength="6" maxlength="20">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="gender"><?php echo YiluPHP::I()->lang('gender'); ?></label>
        </div>
        <div class="col-sm-9">
            <select class="custom-select d-block w-100" id="gender" name="gender">
                <option value="female"><?php echo YiluPHP::I()->lang('gender_female'); ?></option>
                <option value="male"><?php echo YiluPHP::I()->lang('gender_male'); ?></option>
            </select>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="birthday"><?php echo YiluPHP::I()->lang('birthday'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="date" class="form-control" id="birthday" name="birthday" required="1" value="<?php echo date('Y-m-d', strtotime('-23 years')); ?>">
        </div>
    </div>

    <hr class="mb-4">
    <button class="btn btn-primary btn-lg btn-block" type="submit"><?php echo YiluPHP::I()->lang('save'); ?></button>
</form>
<div class="mb-5"></div>

<?php echo load_static('/include/js_jsencrypt.shtml'); ?>
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
                        case "nickname":
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
                                return false;
                            }
                            break;
                        case "mobile":
                            if($.trim(item.value)==""){
                                $(document).dialog({
                                    type: "notice"
                                    ,position: "bottom"
                                    ,dialogClass:"dialog_warn"
                                    ,infoText: getLang("please_input_mobile_number")
                                    ,autoClose: 3000
                                    ,overlayShow: false
                                });
                                return false;
                            }
                            if(!item.value.match(/^\d{6,11}$/)){
                                $(document).dialog({
                                    type: "notice"
                                    ,position: "bottom"
                                    ,dialogClass:"dialog_warn"
                                    ,infoText: getLang("wrong_mobile_number")
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
                                    ,infoText: getLang("please_set_your_password")
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
                                    ,infoText: getLang("password_too_simple")
                                    ,autoClose: 5000
                                    ,overlayShow: false
                                });
                                return false;
                            }
                            break;
                        default:
                            break;
                    }
                    params[item.name] = item.value;
                }

                var toast = loading();
                $.ajax({
                        type: 'post'
                        , dataType: 'json'
                        , url: "<?php echo url_pre_lang(); ?>/user/save_add"
                        , data: params
                        , success: function (data, textStatus, jqXHR) {
                            toast.close();
                            if (data.code == 0) {
                                toast.dialog({
                                    overlayClose: true
                                    , titleShow: false
                                    , content: getLang("save_successfully")
                                    , onClosed: function() {
                                        if (!$("#keeping_form").is(":checked")){
                                            $.getMainHtml("<?php echo url_pre_lang(); ?>/user/list", {with_layout:0,dtype:'json'});
                                        }
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