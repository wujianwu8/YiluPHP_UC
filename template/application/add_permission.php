<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('lang_add_permission'),
];
?>

<h4 class="mb-3"><?php echo $head_info['title']; ?></h4>
<form class="needs-validation title_content" novalidate="" method="post">
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <?php echo YiluPHP::I()->lang('application_name'); ?>
        </div>
        <div class="col-sm-9">
            <?php echo $application_info['app_name']; ?>
            <input type="hidden" name="app_id" value="<?php echo $application_info['app_id']; ?>">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="permission_key"><?php echo YiluPHP::I()->lang('permission_key'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="permission_key" name="permission_key" placeholder="<?php echo YiluPHP::I()->lang('key_rule_notice'); ?>" required="" minlength="3" maxlength="25">
            <div class="invalid-feedback">
                <?php echo YiluPHP::I()->lang('permission_key_rule_notice'); ?>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="permission_name"><?php echo YiluPHP::I()->lang('permission_name'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="permission_name" name="permission_name" required="" minlength="2" maxlength="40">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="description"><?php echo YiluPHP::I()->lang('description'); ?></label>
        </div>
        <div class="col-sm-9">
            <textarea class="form-control" id="description" name="description" maxlength="200"></textarea>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-sm-3 title">
        </div>
        <div class="col-sm-9">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="keeping_form">
                <label class="custom-control-label" for="keeping_form"><?php echo YiluPHP::I()->lang('no_jump_after_saving'); ?></label>
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
                        case "permission_key":
                            if(!item.value.match(/^[a-zA-Z0-9_]{3,25}$/)){
                                $(document).dialog({
                                    type: "notice"
                                    ,position: "bottom"
                                    ,dialogClass:"dialog_warn"
                                    ,infoText: getLang("permission_key_rule_notice")
                                    ,autoClose: 6000
                                    ,overlayShow: false
                                });
                                return false;
                            }
                            if(item.value.toLowerCase().indexOf("grant_")==0){
                                $(document).dialog({
                                    type: "notice"
                                    ,position: "bottom"
                                    ,dialogClass:"dialog_warn"
                                    ,infoText: getLang("permission_key_rule_notice")
                                    ,autoClose: 6000
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
                        , url: "/application/save_add_permission"
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
                                            $.getMainHtml("/application/permission_list/"+params.app_id, {with_layout:0,dtype:'json'});
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
                                    , autoClose: 6000
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