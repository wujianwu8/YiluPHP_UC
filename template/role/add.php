<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('create_role'),
];
?>

<h4 class="mb-3"><?php echo $head_info['title']; ?></h4>
<form class="needs-validation title_content" novalidate="" method="post">
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="role_name"><?php echo YiluPHP::I()->lang('role_name'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="role_name" name="role_name" placeholder="<?php echo YiluPHP::I()->lang('can_be_a_language_key_name'); ?>" required="" maxlength="40">
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

    <hr class="mb-4">
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
                    params[item.name] = item.value;
                }
                var toast = loading();
                $.ajax({
                        type: 'post'
                        , dataType: 'json'
                        , url: "/role/save_add"
                        , data: params
                        , success: function (data, textStatus, jqXHR) {
                            toast.close();
                            if (data.code == 0) {
                                toast.dialog({
                                    overlayClose: true
                                    , titleShow: false
                                    , content: getLang("add_role_succ_and_goto_set_permission")
                                    , onClosed: function() {
                                        $.getMainHtml("/role/grant_permission/"+data.data.role_id, {with_layout:0,dtype:'json'});
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