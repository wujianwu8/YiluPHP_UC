<!--{use_layout layout/admin_main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('add_lang_pack_project'),
];
?>

<h4 class="mb-3"><?php echo $head_info['title']; ?></h4>
<form class="needs-validation title_content" novalidate="" method="post">
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="project_key"><?php echo YiluPHP::I()->lang('project_key'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="project_key" name="project_key" placeholder="<?php echo YiluPHP::I()->lang('key_rule_notice'); ?>" required="" maxlength="30">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="project_name"><?php echo YiluPHP::I()->lang('project_name'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="project_name" name="project_name" placeholder="<?php echo YiluPHP::I()->lang('can_be_a_language_key_name'); ?>" required="" maxlength="40">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="file_dir"><?php echo YiluPHP::I()->lang('lang_pack_storage_dir', ['type'=>'PHP']); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="file_dir" name="file_dir" placeholder="<?php echo YiluPHP::I()->lang('lang_pack_storage_dir', ['type'=>'PHP']); ?>" required="" maxlength="200">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="js_file_dir"><?php echo YiluPHP::I()->lang('lang_pack_storage_dir', ['type'=>'JS']); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="js_file_dir" name="js_file_dir"
                   placeholder="<?php echo YiluPHP::I()->lang('lang_pack_storage_dir', ['type'=>'JS']); ?>" required="" maxlength="200">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="language_types"><?php echo YiluPHP::I()->lang('supported_language_types'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="language_types" name="language_types" placeholder="<?php echo YiluPHP::I()->lang('support_lang_type_rule_brief_notice'); ?>" required="" maxlength="200">
            <div class="invalid-feedback">
                <?php echo YiluPHP::I()->lang('support_lang_type_rule_notice'); ?>
            </div>
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
                    switch (item.name){
                        case "project_key":
                            if(!item.value.match(/^[a-zA-Z0-9_]{3,30}$/)){
                                $(document).dialog({
                                    type: "notice"
                                    ,position: "bottom"
                                    ,dialogClass:"dialog_warn"
                                    ,infoText: getLang("project_key_rule_notice")
                                    ,autoClose: 5000
                                    ,overlayShow: false
                                });
                                return false;
                            }
                            break;
                        case "language_types":
                            if(!item.value.match(/^[a-zA-Z0-9\-,_]{2,200}$/)){
                                $(document).dialog({
                                    type: "notice"
                                    ,position: "bottom"
                                    ,dialogClass:"dialog_warn"
                                    ,infoText: getLang("support_lang_type_rule_notice")
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
                ajaxPost("<?php echo url_pre_lang(); ?>/language/save_add_project", params, function (data) {
                    $(document).dialog({
                        overlayClose: true
                        , titleShow: false
                        , content: getLang("save_successfully")
                        , onClosed: function() {
                            $.getMainHtml("<?php echo url_pre_lang(); ?>/language/project", {with_layout:0,dtype:'json'});
                        }
                    });
                });
            }, false);
        });
    })();
</script>