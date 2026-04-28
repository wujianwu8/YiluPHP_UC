<!--{use_layout layout/admin_main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('edit_invitation_link'),
];
?>

<h4 class="mb-3"><?php echo YiluPHP::I()->lang('edit_invitation_link'); ?></h4>
<form class="needs-validation title_content" novalidate="" method="post">
    <input type="hidden" name="id" value="<?php echo $link_info['id']; ?>">
    <div class="row mb-2">
        <div class="col-sm-3 title">ID</div>
        <div class="col-sm-9"><?php echo $link_info['id']; ?></div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">UID</div>
        <div class="col-sm-9">
            <?php if(isset($owner_info)): ?>
                <img src="<?php echo $owner_info['avatar']; ?>" width="18" height="18">
                <?php echo $owner_info['nickname']; ?>
                (<?php echo $link_info['uid']; ?>)
            <?php else: ?>
                <?php echo $link_info['uid']; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title"><?php echo YiluPHP::I()->lang('invite_scene'); ?></div>
        <div class="col-sm-9"><?php echo $link_info['scene']; ?></div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title"><?php echo YiluPHP::I()->lang('invite_code'); ?></div>
        <div class="col-sm-9"><?php echo $link_info['invite_code']; ?></div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="remark"><?php echo YiluPHP::I()->lang('remark'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="remark" name="remark" value="<?php echo $link_info['remark']; ?>">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="cookie_ttl"><?php echo YiluPHP::I()->lang('cookie_ttl'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="number" class="form-control" id="cookie_ttl" name="cookie_ttl" value="<?php echo $link_info['cookie_ttl']; ?>">
        </div>
    </div>

    <hr class="mb-4">
    <button class="btn btn-primary btn-lg btn-block" type="submit"><?php echo YiluPHP::I()->lang('save'); ?></button>
</form>
<div class="mb-5"></div>
<script>
    (function() {
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                event.stopPropagation();
                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return false;
                }

                var params = {dtype:"json"};
                var inputs = $(form).serializeArray();
                for(var index in inputs){
                    params[inputs[index].name] = inputs[index].value;
                }

                var toast = loading();
                $.ajax({
                    type: 'post'
                    , dataType: 'json'
                    , url: "<?php echo url_pre_lang(); ?>/invitation/save_edit"
                    , data: params
                    , success: function (data) {
                        toast.close();
                        if (data.code == 0) {
                            toast.dialog({
                                overlayClose: true
                                , titleShow: false
                                , content: getLang("save_successfully")
                                , onClosed: function() {
                                    $.getMainHtml("<?php echo url_pre_lang(); ?>/invitation/list", {with_layout:0,dtype:'json'});
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
                    , error: function (XMLHttpRequest, textStatus) {
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
                });
            }, false);
        });
    })();
</script>
