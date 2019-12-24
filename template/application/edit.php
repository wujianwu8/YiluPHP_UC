<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => '编辑应用',
];
?>

<h4 class="mb-3">编辑应用</h4>
<form class="needs-validation title_content" novalidate="" method="post">
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="app_id">应用ID</label>
        </div>
        <div class="col-sm-9">
            <input type="hidden" name="app_id" value="<?php echo $app_info['app_id'];?>">
            <?php echo $app_info['app_id'];?>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            应用名称
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="app_name" name="app_name" required="" minlength="3" maxlength="30" value="<?php echo $app_info['app_name'];?>">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="index_url">应用首页地址</label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="index_url" name="index_url" value="<?php echo $app_info['index_url'];?>">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="app_white_ip">服务器IP白名单</label>
        </div>
        <div class="col-sm-9">
            <textarea class="form-control" id="app_white_ip" name="app_white_ip" placeholder="多个IP使用半角逗号或换行分隔，不能有空格" required=""><?php echo $app_info['app_white_ip'];?></textarea>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="status">状态</label>
        </div>
        <div class="col-sm-9">
            <select class="custom-select d-block w-100" id="status" name="status">
                <option value="0" <?php echo $app_info['status']==0?'selected':'';?> ><?php echo $app->lang('application_status_0'); ?></option>
                <option value="1" <?php echo $app_info['status']==1?'selected':'';?> ><?php echo $app->lang('application_status_1'); ?></option>
            </select>
        </div>
    </div>

    <hr class="mb-4">
    <button class="btn btn-primary btn-lg btn-block" type="submit">保存</button>
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
                        case "app_white_ip":
                            if(!item.value.match(/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}[\,|\r|\n]*)+$/)){
                                $(document).dialog({
                                    type: "notice"
                                    ,position: "bottom"
                                    ,dialogClass:"dialog_warn"
                                    ,infoText: "白名单IP设置错误，请检查"
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
                        , url: "/application/save_edit"
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
                                            $.getMainHtml("/application/list", {with_layout:0,dtype:'json'});
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