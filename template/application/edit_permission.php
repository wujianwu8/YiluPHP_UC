<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => $app->lang('lang_edit_permission'),
];
?>

<h4 class="mb-3"><?php echo $head_info['title']; ?></h4>
<form class="needs-validation title_content" novalidate="" method="post">
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <?php echo $app->lang('application_name'); ?>
        </div>
        <div class="col-sm-9">
            <?php echo $application_info['app_name']; ?>
            <input type="hidden" name="app_id" value="<?php echo $application_info['app_id']; ?>">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <?php echo $app->lang('permission_key'); ?>
        </div>
        <div class="col-sm-9">
            <input type="hidden" name="permission_key" value="<?php echo $permission_info['permission_key']; ?>">
            <?php echo $permission_info['permission_key']; ?>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <?php echo $app->lang('translation'); ?>
        </div>
        <div class="col-sm-9">
            <?php echo $permission_info['permission_name_lang']; ?>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="permission_name"><?php echo $app->lang('permission_name'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="permission_name" name="permission_name" required="" minlength="2" maxlength="40" value="<?php echo $permission_info['permission_name']; ?>">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="description"><?php echo $app->lang('description'); ?></label>
        </div>
        <div class="col-sm-9">
            <textarea class="form-control" id="description" name="description" maxlength="200"><?php echo $permission_info['description']; ?></textarea>
        </div>
    </div>
    <div class="row mb-5">
        <div class="col-sm-3 title">
        </div>
        <div class="col-sm-9">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="keeping_form">
                <label class="custom-control-label" for="keeping_form"><?php echo $app->lang('no_jump_after_saving'); ?></label>
            </div>
        </div>
    </div>

    <button class="btn btn-primary btn-lg btn-block" type="submit"><?php echo $app->lang('save'); ?></button>
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
                    params[item.name] = item.value;
                }

                var toast = loading();
                $.ajax({
                        type: 'post'
                        , dataType: 'json'
                        , url: "/application/save_edit_permission"
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