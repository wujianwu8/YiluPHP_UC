<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => $app->lang('details_of_complaint'),
];
?>

<div class="title_content">
    <div class="row">
        <h1 class="h5"><?php echo $complaint_info['title']; ?></h1>
    </div>
    <div class="row mb-2" id="complaint_detail">
        <div class="mr-5">
            <span class="title mr-2"><?php echo $app->lang('complainant')?></span>
            <span>
                <a class="detail" href="/user/detail/<?php echo $complaint_info['complaint_uid']; ?>" uid="<?php echo $complaint_info['complaint_uid']; ?>">
                    <img src="<?php echo $complaint_info['complaint_avatar']; ?>" width="20" height="20">
                    <?php echo $complaint_info['complaint_nickname']; ?>
                </a>
            </span>
        </div>
        <div class="mr-5">
            <span class="title mr-2"><?php echo $app->lang('respondent')?></span>
            <span>
                <a class="detail" href="/user/detail/<?php echo $complaint_info['respondent_uid']; ?>" uid="<?php echo $complaint_info['respondent_uid']; ?>">
                    <img src="<?php echo $complaint_info['respondent_avatar']; ?>" width="20" height="20">
                    <?php echo $complaint_info['respondent_nickname']; ?>
                </a>
            </span>
        </div>
        <div>
            <span class="title"><?php echo $app->lang('complaint_time')?></span>
            <span>
                <?php echo date('Y-m-d H:i:s', $complaint_info['ctime']); ?>
            </span>
        </div>
    </div>
    <div class="row mb-3">
        <fieldset>
            <legend><?php echo $app->lang('complaint_content')?></legend>
            <?php echo $complaint_info['content']; ?>
        </fieldset>
    </div>
    <form class="needs-validation" novalidate>
        <input type="hidden" name="id" value="<?php echo $complaint_info['id']; ?>">
        <div class="row mb-3">
            <div class="col-md-2 title"><?php echo $app->lang('status')?></div>
            <div class="col-md-4">
                <select name="status">
                    <option value="0"<?php echo $complaint_info['status']=='0'? ' selected':''; ?>>
                        <?php echo $app->lang('complaint_status_0'); ?>
                    </option>
                    <option value="1"<?php echo $complaint_info['status']=='1'? ' selected':''; ?>>
                        <?php echo $app->lang('complaint_status_1'); ?>
                    </option>
                    <option value="2"<?php echo $complaint_info['status']=='2'? ' selected':''; ?>>
                        <?php echo $app->lang('complaint_status_2'); ?>
                    </option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-2 title"><?php echo $app->lang('remark')?></div>
            <div class="col-md-10">
                <textarea class="w-100" id="remark" name="remark"><?php echo $complaint_info['remark']; ?></textarea>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-2">
            </div>
            <div class="col-md-10">
                <button class="btn btn-primary btn-sm pl-5 pr-5" type="submit"><?php echo $app->lang('save')?></button>
                <button class="btn btn-light btn-sm ml-3 pl-5 pr-5" type="button" onclick="window.close();"><?php echo $app->lang('close_this_page')?></button>
            </div>
        </div>
    </form>
</div>



<script>

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
                if(item.name=="id"){
                    params.id = item.value;
                }
                if(item.name=="status"){
                    params.status = item.value;
                }
                if(item.name=="remark"){
                    params.remark = item.value;
                }
            }

            var toast = loading();
            $.ajax({
                    type: 'post'
                    , dataType: 'json'
                    , url: "/complaint/save_edit"
                    , data: params
                    , success: function (data, textStatus, jqXHR) {
                        toast.close();
                        if (data.code == 0) {
                            $(document).dialog({
                                type: "notice"
                                , position: "bottom"
                                , infoText: getLang("save_successfully")
                                , autoClose: 2000
                                , overlayShow: false
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

    $("#complaint_detail").click(function(e){
        var obj = null;
        if(e.target.tagName.toLocaleUpperCase() == "A"){
            obj = $(e.target);
        }
        else if(e.target.parentNode.tagName.toLocaleUpperCase() == "A"){
            obj = $(e.target.parentNode);
        }
        if(obj!==null) {
            if (obj.hasClass("detail")) {
                e.preventDefault();
                uid = obj.attr("uid");
                nickname = obj.text();
                dialogShowUserInfo(uid, nickname);
            }
        }
    });

    $(function(){
        autoTextarea(document.getElementById("remark"));
    });
</script>
