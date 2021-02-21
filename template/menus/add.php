<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('add_menu'),
];
?>

<h4 class="mb-3"><?php echo $head_info['title']; ?></h4>
<form class="needs-validation" novalidate="" method="post">
    <div class="d-block my-3">
        <label for="position"><?php echo YiluPHP::I()->lang('menu_position'); ?></label>
        <div class="custom-control custom-radio">
            <input id="positionLeft" name="position" value="LEFT" type="radio" class="custom-control-input" checked="true" required="">
            <label class="custom-control-label" for="positionLeft"><?php echo YiluPHP::I()->lang('left_menu'); ?></label>
        </div>
        <div class="custom-control custom-radio">
            <input id="positionTop" name="position" value="TOP" type="radio" class="custom-control-input" required="">
            <label class="custom-control-label" for="positionTop"><?php echo YiluPHP::I()->lang('head_menu'); ?></label>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="parent_menu"><?php echo YiluPHP::I()->lang('parent_level'); ?></label>
            <select class="custom-select d-block w-100" id="parent_menu" name="parent_menu" required="">
                <option value="0"><?php echo YiluPHP::I()->lang('top_level'); ?></option>
                <?php
                    $position = '';
                    foreach($parent_menus as $menu){
                ?>
                    <?php if($position!=$menu['position']){ ?>
                    <option disabled><?php echo $menu['position']=='TOP'?YiluPHP::I()->lang('head_menu'):YiluPHP::I()->lang('left_menu'); ?></option>
                    <?php $position=$menu['position']; } ?>
                <option value="<?php echo $menu['id'] ?>"><?php echo $menu['lang_key'] ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label for="lang_key"><?php echo YiluPHP::I()->lang('menu_name'); ?></label>
        <input type="text" class="form-control" id="lang_key" name="lang_key" placeholder="<?php echo YiluPHP::I()->lang('can_be_a_language_key_name'); ?>" required="">
        <div class="invalid-feedback">
            <?php echo YiluPHP::I()->lang('enter_lang_key_usefulness'); ?>
        </div>
    </div>

    <div class="mb-3">
        <label for="active_preg"><?php echo YiluPHP::I()->lang('check_status_matching_rule'); ?></label>
        <input type="text" class="form-control" id="active_preg" name="active_preg" placeholder="<?php echo YiluPHP::I()->lang('regexp_such_as'); ?>" required="">
        <div class="invalid-feedback">
            <?php echo YiluPHP::I()->lang('selected_menu_regexp_rule_notice'); ?>
        </div>
    </div>

    <div class="mb-3">
        <label for="href"><?php echo YiluPHP::I()->lang('jump_link'); ?></label>
        <input type="text" class="form-control" id="href" name="href" placeholder="<?php echo YiluPHP::I()->lang('jump_link'); ?>">
    </div>

    <div class="mb-3">
        <label for="link_class"><?php echo YiluPHP::I()->lang('link_class'); ?></label>
        <input type="text" class="form-control" id="link_class" name="link_class" placeholder="<?php echo YiluPHP::I()->lang('additional_class_for_the_link'); ?>">
    </div>

    <div class="mb-3">
        <label for="target"><?php echo YiluPHP::I()->lang('link_target'); ?></label>
        <input type="text" class="form-control" id="target" name="target" placeholder="<?php echo YiluPHP::I()->lang('value_of_target_attr_of_the_link'); ?>">
    </div>

    <div class="mb-3">
        <label for="weight"><?php echo YiluPHP::I()->lang('sort'); ?></label>
        <input type="number" class="form-control" id="weight" name="weight" placeholder="<?php echo YiluPHP::I()->lang('the_bigger_number_the_later'); ?>" required="" value="500">
        <div class="invalid-feedback">
            <?php echo YiluPHP::I()->lang('please_ente_sort_number'); ?>
        </div>
    </div>

    <div class="mb-3">
        <label for="icon"><?php echo YiluPHP::I()->lang('icon_style_or_html_code'); ?></label>
        <input type="text" class="form-control" id="icon" name="icon" placeholder="<?php echo YiluPHP::I()->lang('the_icon_ahead_menu'); ?>">
    </div>

    <div class="mb-3">
        <label for="permission"><?php echo YiluPHP::I()->lang('access_required_permission'); ?></label>
        <input type="text" class="form-control" id="permission" name="permission" placeholder="<?php echo YiluPHP::I()->lang('formatted_permission_key_notice'); ?>">
    </div>

    <div class="mb-3">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="keeping_form">
            <label class="custom-control-label" for="keeping_form"><?php echo YiluPHP::I()->lang('no_jump_after_saving'); ?></label>
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
                        , url: "/menus/save_add"
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
                                            $.getMainHtml("/menus/list", {with_layout:0,dtype:'json'});
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