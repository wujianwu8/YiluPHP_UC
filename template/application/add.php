<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => '创建应用',
];
?>

<h4 class="mb-3">创建应用</h4>
<form class="needs-validation title_content" novalidate="" method="post">
    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="app_id">应用ID</label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="app_id" name="app_id" placeholder="由字母、数字、下划线组成" required="" minlength="3" maxlength="20">
            <div class="invalid-feedback">
                只能包含字母、数字、下划线组成
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="app_name">应用名称</label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="app_name" name="app_name" required="" minlength="3" maxlength="30">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="index_url">应用首页地址</label>
        </div>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="index_url" name="index_url">
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="app_white_ip">服务器IP白名单</label>
        </div>
        <div class="col-sm-9">
            <textarea class="form-control" id="app_white_ip" name="app_white_ip" placeholder="多个IP使用半角逗号或换行分隔，不能有空格"></textarea>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-3 title">
            <label for="status">状态</label>
        </div>
        <div class="col-sm-9">
            <select class="custom-select d-block w-100" id="status" name="status">
                <option value="0"><?php echo $app->lang('application_status_0'); ?></option>
                <option value="1"><?php echo $app->lang('application_status_1'); ?></option>
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
                        case "app_id":
                            if(!item.value.match(/^[a-zA-Z0-9_]{3,20}$/)){
                                $(document).dialog({
                                    type: "notice"
                                    ,position: "bottom"
                                    ,dialogClass:"dialog_warn"
                                    ,infoText: "应用ID只能使用字母、数字、下划线，长度在3-20个字"
                                    ,autoClose: 5000
                                    ,overlayShow: false
                                });
                                return false;
                            }
                            break;
                        case "app_white_ip":
                            if($.trim(item.value)!="" && !item.value.match(/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}[\,|\r|\n]*)+$/)){
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
                        , url: "/application/save_add"
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