<!--{use_layout layout/main}-->
<?php
if ($page_name == 'role_permission'){
    $head_info = [
        'title' => YiluPHP::I()->lang('permission_of_role', ['role_name'=>$role_info['role_name']]),
    ];
}
else if($page_name=='user_permission'){
    $head_info = [
        'title' => YiluPHP::I()->lang('permission_of_user', ['nickname'=>$user_info['nickname']]),
    ];
}
?>

<h4>
    <?php echo $head_info['title']; ?>
    <?php if ($page_name == 'role_permission'){ ?>
        <input type="hidden" id="param_id" name="role_id" value="<?php echo $role_info['id']; ?>">
    <?php } else if ($page_name == 'user_permission'){ ?>
        <input type="hidden" id="param_id" name="uid" value="<?php echo $user_info['uid']; ?>">
    <?php } ?>
    <select class="ml-5" name="app_id" onchange="changeApplication(this)">
        <?php foreach($app_list as $item): ?>
        <option value="<?php echo $item['app_id']; ?>" <?php echo $item['app_id']==$current_app_id?'selected':''; ?>>
            <?php echo $item['app_name']; ?>
        </option>
        <?php endforeach; ?>
    </select>
</h4>
<div class="table-responsive">
    <table class="table table-striped table-sm table_list" id="all_permission_list">
        <thead>
        <tr>
            <th></th>
            <th>ID</th>
            <th><?php echo YiluPHP::I()->lang('permission_name'); ?></th>
            <th><?php echo YiluPHP::I()->lang('permission_key'); ?></th>
            <th><?php echo YiluPHP::I()->lang('description'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($app_permission_list as $item): ?>
        <tr>
            <td>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="permission_id"
                           id="permission_id<?php echo $item['permission_id']; ?>"
                           value="<?php echo $item['permission_id']; ?>"
                            <?php echo in_array($item['permission_id'], $having_permission_ids)?'checked':''; ?>>
                    <label class="custom-control-label" for="permission_id<?php echo $item['permission_id']; ?>"></label>
                </div>
            </td>
            <td><?php echo $item['permission_id']; ?></td>
            <td><?php echo $item['permission_name_lang']; ?></td>
            <td><?php echo $item['permission_key']; ?></td>
            <td class="show_title" title="<?php echo htmlspecialchars($item['description']); ?>"><?php echo mb_substr($item['description'],0,60); ?></td>
        </tr>
        <?php endforeach; ?>

        <?php if(empty($app_permission_list)): ?>
            <tr>
                <td colspan="5" class="pt-5 pb-5"><center><?php echo YiluPHP::I()->lang('no_data'); ?></center></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>

    function changeApplication(_this){
        if ($("#param_id").attr("name") == "role_id"){
            $.getMainHtml("/role/grant_permission/"+$("#param_id").val()+"?app_id="+_this.value, {with_layout:0,dtype:'json'});
        }
        else{
            $.getMainHtml("/user/grant_permission/"+$("#param_id").val()+"?app_id="+_this.value, {with_layout:0,dtype:'json'});
        }
    }

    $("#all_permission_list").click(function(e){
        var obj = null;
        if(e.target.tagName.toLocaleUpperCase() == "INPUT"){
            obj = $(e.target);
        }

        if(obj!==null) {
            var params = {
                dtype:"json"
                ,permission_id: obj.val()
            };
            if ($("#param_id").attr("name") == "role_id") {
                params.role_id = $("#param_id").val();
                if (obj.prop('checked')) {
                    url = "/role/save_add_role_permission";
                } else {
                    url = "/role/save_delete_role_permission";
                }
            }
            else{
                params.uid = $("#param_id").val();
                if (obj.prop('checked')) {
                    url = "/user/save_add_permission";
                } else {
                    url = "/user/save_delete_permission";
                }
            }
            var toast = loading();
            $.ajax({
                type: 'post'
                , dataType: 'json'
                , url: url
                , data: params
                , success: function (data, textStatus, jqXHR) {
                    toast.close();
                    if (data.code != 0) {
                        if(obj.prop('checked')){
                            obj.prop("checked", false);
                        }
                        else{
                            obj.prop("checked", true);
                        }
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
                    if(obj.prop('checked')){
                        obj.prop("checked", false);
                    }
                    else{
                        obj.prop("checked", true);
                    }
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
        }
    });

    $('.show_title').tooltip({
        placement: 'top',
        viewport: {
            selector: '.container-viewport',
            padding: 2
        }
    })

</script>
