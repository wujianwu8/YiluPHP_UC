<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => $app->lang('edit_menu'),
];
?>

<h4 class="mb-3"><?php echo $head_info['title']; ?></h4>
<form class="needs-validation" novalidate="" method="post">
    <input name="id" value="<?php echo $menu_info['id']; ?>" type="hidden">
    <div class="d-block my-3">
        <label><?php echo $app->lang('menu_position'); ?></label>
        <div class="custom-control custom-radio">
            <input id="positionLeft" name="position" value="LEFT" type="radio" class="custom-control-input"
                <?php echo $menu_info['position']=='LEFT'?'checked="true"' :''; ?>
                    <?php echo $menu_info['type']=='SYSTEM'?'disabled' :''; ?> >
            <label class="custom-control-label" for="positionLeft"><?php echo $app->lang('left_menu'); ?></label>
        </div>
        <div class="custom-control custom-radio">
            <input id="positionTop" name="position" value="TOP" type="radio" class="custom-control-input"
                <?php echo $menu_info['position']=='TOP'?'checked="true"' :''; ?>
                <?php echo $menu_info['type']=='SYSTEM'?'disabled' :''; ?> >
            <label class="custom-control-label" for="positionTop"><?php echo $app->lang('head_menu'); ?></label>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="parent_menu"><?php echo $app->lang('parent_level'); ?></label>
            <select class="custom-select d-block w-100" id="parent_menu" name="parent_menu"
                <?php echo $menu_info['type']=='SYSTEM'?'disabled' :''; ?> >
                <option value="0" <?php echo $menu_info['parent_menu']==0?'selected' : ''; ?> ><?php echo $app->lang('top_level'); ?></option>
                <?php
                    $position = '';
                    foreach($parent_menus as $menu){
                ?>
                    <?php if($position!=$menu['position']){ ?>
                    <option disabled><?php echo $menu['position']=='TOP'?$app->lang('head_menu'):$app->lang('left_menu'); ?></option>
                    <?php $position=$menu['position']; } ?>
                <option value="<?php echo $menu['id'] ?>" <?php echo $menu_info['parent_menu']==$menu['id']?'selected' : ''; ?> ><?php echo $menu['lang_key'] ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label for="lang_key">
            <?php echo $app->lang('menu_name'); ?>
            (<?php echo $app->lang($menu_info['lang_key']); ?>)
        </label>
        <input type="text" class="form-control" id="lang_key" name="lang_key" placeholder="<?php echo $app->lang('can_be_a_language_key_name'); ?>"
               value="<?php echo $menu_info['lang_key']; ?>"
            <?php echo $menu_info['type']=='SYSTEM'?'disabled' :''; ?> >
        <div class="invalid-feedback">
            <?php echo $app->lang('enter_lang_key_usefulness'); ?>
        </div>
    </div>

    <div class="mb-3">
        <label for="active_preg"><?php echo $app->lang('check_status_matching_rule'); ?></label>
        <input type="text" class="form-control" id="active_preg" name="active_preg" placeholder="<?php echo $app->lang('regexp_such_as'); ?>"
               value="<?php echo $menu_info['active_preg']; ?>"
                    <?php echo $menu_info['type']=='SYSTEM'?'disabled' :''; ?> >
        <div class="invalid-feedback">
            <?php echo $app->lang('selected_menu_regexp_rule_notice'); ?>
        </div>
    </div>

    <div class="mb-3">
        <label for="href"><?php echo $app->lang('jump_link'); ?></label>
        <input type="text" class="form-control" id="href" name="href" placeholder="<?php echo $app->lang('jump_link'); ?>" value="<?php echo $menu_info['href']; ?>"
            <?php echo $menu_info['type']=='SYSTEM'?'disabled' :''; ?> >
    </div>

    <div class="mb-3">
        <label for="link_class"><?php echo $app->lang('link_class'); ?></label>
        <input type="text" class="form-control" id="link_class" name="link_class" placeholder="<?php echo $app->lang('additional_class_for_the_link'); ?>" value="<?php echo $menu_info['link_class']; ?>"
            <?php echo $menu_info['type']=='SYSTEM'?'disabled' :''; ?> >
    </div>

    <div class="mb-3">
        <label for="target"><?php echo $app->lang('link_target'); ?></label>
        <input type="text" class="form-control" id="target" name="target" placeholder="<?php echo $app->lang('value_of_target_attr_of_the_link'); ?>"
               value="<?php echo $menu_info['target']; ?>"
                    <?php echo $menu_info['type']=='SYSTEM'?'disabled' :''; ?>>
    </div>

    <div class="mb-3">
        <label for="weight"><?php echo $app->lang('sort'); ?></label>
        <input type="number" class="form-control" id="weight" name="weight" placeholder="<?php echo $app->lang('the_bigger_number_the_later'); ?>" required="" value="<?php echo $menu_info['weight']; ?>">
        <div class="invalid-feedback">
            <?php echo $app->lang('please_ente_sort_number'); ?>
        </div>
    </div>

    <div class="mb-3">
        <label for="icon"><?php echo $app->lang('icon_style_or_html_code'); ?></label>
        <input type="text" class="form-control" id="icon" name="icon" placeholder="<?php echo $app->lang('the_icon_ahead_menu'); ?>"
               value="<?php echo htmlspecialchars($menu_info['icon']); ?>"
            <?php echo $menu_info['type']=='SYSTEM'?'disabled' :''; ?> >
    </div>

    <div class="mb-3">
        <label for="permission"><?php echo $app->lang('access_required_permission'); ?></label>
        <input type="text" class="form-control" id="permission" name="permission" placeholder="<?php echo $app->lang('formatted_permission_key_notice'); ?>"
               value="<?php echo $menu_info['permission']?$menu_info['permission']:''; ?>"
                    <?php echo $menu_info['type']=='SYSTEM'?'disabled' :''; ?> >
    </div>

    <div class="mb-3">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="keeping_form">
            <label class="custom-control-label" for="keeping_form"><?php echo $app->lang('no_jump_after_saving'); ?></label>
        </div>
    </div>

    <hr class="mb-4">
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
                        , url: "/menus/save_edit"
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