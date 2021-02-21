<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('menu_complained_user'),
];
?>

<form class="needs-validation" novalidate>
    <div class="row mb-2">
        <div class="col-md-2">
            <select class="custom-select d-block w-100" name="status">
                <option value=""><?php echo YiluPHP::I()->lang('status'); ?></option>
                <option value="0"<?php echo isset($_REQUEST['status'])&&$_REQUEST['status']=='0'? ' selected':''; ?>>
                    <?php echo YiluPHP::I()->lang('complaint_status_0'); ?>
                </option>
                <option value="1"<?php echo isset($_REQUEST['status'])&&$_REQUEST['status']=='1'? ' selected':''; ?>>
                    <?php echo YiluPHP::I()->lang('complaint_status_1'); ?>
                </option>
                <option value="2"<?php echo isset($_REQUEST['status'])&&$_REQUEST['status']=='2'? ' selected':''; ?>>
                    <?php echo YiluPHP::I()->lang('complaint_status_2'); ?>
                </option>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" name="keyword" placeholder="<?php echo YiluPHP::I()->lang('search_keyword'); ?>" value="<?php echo isset($_REQUEST['keyword'])? $_REQUEST['keyword']:''; ?>">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="complaint_user" placeholder="<?php echo YiluPHP::I()->lang('complainant_id_or_nickname'); ?>" value="<?php echo isset($_REQUEST['complaint_user'])? $_REQUEST['complaint_user']:''; ?>">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="respondent_user" placeholder="<?php echo YiluPHP::I()->lang('respondent_id_or_nickname'); ?>" value="<?php echo isset($_REQUEST['respondent_user'])? $_REQUEST['respondent_user']:''; ?>">
        </div>
    </div>
    <div class="row mb-3">
        <button class="btn btn-primary btn-sm ml-3 pl-5 pr-5" type="submit"><?php echo YiluPHP::I()->lang('search'); ?></button>
        <button class="btn btn-primary btn-sm ml-4" type="button" id="clear_form"><?php echo YiluPHP::I()->lang('clean_up'); ?></button>
        <select class="ml-4" name="page_size">
            <option value="10">10<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
            <option value="15"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='15'? ' selected':''; ?>>15<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
            <option value="20"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='20'? ' selected':''; ?>>20<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
            <option value="30"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='30'? ' selected':''; ?>>30<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
            <option value="40"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='40'? ' selected':''; ?>>40<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
            <option value="50"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='50'? ' selected':''; ?>>50<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
            <option value="100"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='100'? ' selected':''; ?>>100<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
            <option value="200"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='200'? ' selected':''; ?>>200<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
            <option value="500"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='500'? ' selected':''; ?>>500<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
        </select>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-sm" id="all_complaint_list">
        <thead>
        <tr>
            <th>ID</th>
            <th colspan="2"><?php echo YiluPHP::I()->lang('respondent'); ?></th>
            <th colspan="2"><?php echo YiluPHP::I()->lang('complainant'); ?></th>
            <th><?php echo YiluPHP::I()->lang('title'); ?></th>
            <th><?php echo YiluPHP::I()->lang('content'); ?></th>
            <th><?php echo YiluPHP::I()->lang('status'); ?></th>
            <th><?php echo YiluPHP::I()->lang('remark'); ?></th>
            <th><?php echo YiluPHP::I()->lang('complaint_time'); ?></th>
            <th><?php echo YiluPHP::I()->lang('operation'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data_list as $item): ?>
        <tr _id="<?php echo $item['id']; ?>" <?php echo $item['status']==2?' class="text-gray"':''; ?>>
            <td><?php echo $item['id']; ?></td>
            <td><img src="<?php echo $item['respondent_avatar']; ?>" width="18" height="18"></td>
            <td>
                <a href="/user/detail/<?php echo $item['respondent_uid']; ?>" uid="<?php echo $item['respondent_uid']; ?>">
                    <?php echo $item['respondent_nickname']; ?>
                </a>
            </td>
            <td><img src="<?php echo $item['complaint_avatar']; ?>" width="18" height="18"></td>
            <td>
                <a href="/user/detail/<?php echo $item['complaint_uid']; ?>" uid="<?php echo $item['complaint_uid']; ?>">
                    <?php echo $item['complaint_nickname']; ?>
                </a>
            </td>
            <td><?php echo $item['title']; ?></td>
            <td><?php echo mb_substr(strip_tags($item['content']), 0, 50); ?></td>
            <td>
                <select name="status" last_value="<?php echo $item['status']; ?>">
                    <option value="0"<?php echo $item['status']=='0'? ' selected':''; ?>>
                        <?php echo YiluPHP::I()->lang('complaint_status_0'); ?>
                    </option>
                    <option value="1"<?php echo $item['status']=='1'? ' selected':''; ?>>
                        <?php echo YiluPHP::I()->lang('complaint_status_1'); ?>
                    </option>
                    <option value="2"<?php echo $item['status']=='2'? ' selected':''; ?>>
                        <?php echo YiluPHP::I()->lang('complaint_status_2'); ?>
                    </option>
                </select>
            </td>
            <td><?php echo mb_substr(strip_tags($item['remark']), 0, 50); ?></td>
            <td><?php echo date('Y-m-d H:i:s', $item['ctime']); ?></td>
            <td>
                <a class="detail" href="/complaint/detail/<?php echo $item['id']; ?>" target="_blank">
                    <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if(empty($data_list)): ?>
            <tr>
                <td colspan="11" class="pt-5 pb-5"><center><?php echo YiluPHP::I()->lang('no_data'); ?></center></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php echo pager::I()->display_pages([
    'data_count' => $data_count,
    'page' => $page,
    'page_size' => $page_size,
    'a_class' => 'ajax_main_content',
    'first_page_text' => YiluPHP::I()->lang('first_page'),
    'pre_page_text' => YiluPHP::I()->lang('previous_page'),
    'next_page_text' => YiluPHP::I()->lang('next_page'),
    'last_page_text' => YiluPHP::I()->lang('last_page'),
]); ?>


<script>

    function changeComplaintStatus(obj){
        var params = {
            dtype:"json"
            ,id:obj.parents("tr").attr("_id")
        };
        params[obj.attr("name")] = obj.val();
        var toast = loading();
        $.ajax({
                type: 'post'
                , dataType: 'json'
                , url: "/complaint/save_edit"
                , data: params
                , success: function (data, textStatus, jqXHR) {
                    toast.close();
                    if (data.code == 0) {
                        obj.attr("last_value", obj.val());
                        if (params.status==2){
                            obj.parents("tr").addClass("text-gray");
                        }
                        else{
                            obj.parents("tr").removeClass("text-gray");
                        }
                        $(document).dialog({
                            type: "notice"
                            , position: "bottom"
                            , infoText: getLang("save_successfully")
                            , autoClose: 2000
                            , overlayShow: false
                        });
                    }
                    else {
                        obj.val(obj.attr("last_value"));
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
                    obj.val(obj.attr("last_value"));
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
    }

    $("#all_complaint_list").find("select").change(function(){
        changeComplaintStatus($(this));
    });

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
                with_layout:0
                ,dtype:"json"
            };
            var arr = [];
            var inputs = $(form).serializeArray();
            for(var index in inputs){
                var item = inputs[index];
                if($.trim(item.value)==''){
                    continue;
                }
                arr.push(item.name+"="+item.value);
            }
            url = "/complaint/list";
            if(arr.length>0){
                url = url+"?"+arr.join("&");
            }
            $.getMainHtml(url, params);
        }, false);
    });

    $("#all_complaint_list").click(function(e){
        var obj = null;
        if(e.target.tagName.toLocaleUpperCase() == "A"){
            obj = $(e.target);
        }
        else if(e.target.parentNode.tagName.toLocaleUpperCase() == "A"){
            obj = $(e.target.parentNode);
        }

        if(obj!==null) {
            if (!obj.hasClass("detail")) {
                e.preventDefault();
                uid = obj.attr("uid");
                nickname = obj.text();
                dialogShowUserInfo(uid, nickname);
            }
        }
    });

    $("#clear_form").click(function(e){
        $("#clear_form").parents("form").find("input").val("");
        $("#clear_form").parents("form").find("select[name=status]").val("");
    });

</script>
