<!--{use_layout layout/admin_main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('add_invitation_link'),
];
?>

<h4 class="mb-3"><?php echo YiluPHP::I()->lang('add_invitation_link'); ?></h4>
<form class="needs-validation title_content" novalidate="" method="post">
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="uid">UID</label>
        </div>
        <div class="col-sm-9">
            <input type="number" class="form-control" id="uid" name="uid" required="">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="scene"><?php echo YiluPHP::I()->lang('invite_scene'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="scene" name="scene" required="" placeholder="e.g. register">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="remark"><?php echo YiluPHP::I()->lang('remark'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="remark" name="remark">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="cookie_ttl"><?php echo YiluPHP::I()->lang('cookie_ttl'); ?></label>
        </div>
        <div class="col-sm-9">
            <input type="number" class="form-control" id="cookie_ttl" name="cookie_ttl" placeholder="<?php echo YiluPHP::I()->lang('default'); ?> 1296000 (15 days)">
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
                    , url: "<?php echo url_pre_lang(); ?>/invitation/save_add"
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
