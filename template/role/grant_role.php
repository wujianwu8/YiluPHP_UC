<!--{use_layout layout/main}-->
<?php
    $head_info = [
        'title' => $app->lang('role_of_user', ['nickname'=>$user_info['nickname']]),
    ];
?>

<style>
    #all_role_list .custom-control {
        display: inline-block;
        margin-right: 1rem;
        margin-bottom: 0.5rem;
        padding: 2px 5px 1px 24px;
    }
    #all_role_list .custom-control-label::after,
    #all_role_list .custom-control-label::before{
        left: 5px;
    }
    #all_role_list .custom-control:hover{
        border: #007bff 1px solid;
        border-radius: 4px;
        color: #007bff;
        padding: 0px 4px 0px 23px;
    }
    #all_role_list .custom-control:hover .custom-control-label::after,
    #all_role_list .custom-control:hover .custom-control-label::before{
        top: 0.1rem;
        left: 4px;
    }
</style>

<h4 class="mb-4">
    <?php echo $head_info['title']; ?>
</h4>
<div id="all_role_list">
    <input type="hidden" id="uid" name="uid" value="<?php echo $user_info['uid']; ?>">
    <?php foreach($role_list as $item): ?>
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" name="role_id" id="role_id_<?php echo $item['id']; ?>" value="<?php echo $item['id']; ?>" <?php echo $item['is_own']?'checked':''; ?>>
            <label class="custom-control-label show_title" for="role_id_<?php echo $item['id']; ?>" title="<?php echo htmlspecialchars($item['description']); ?>"><?php echo $item['role_name_lang']; ?></label>
        </div>
    <?php endforeach; ?>

    <?php if(empty($role_list)): ?>
        <div class="pt-5 pb-5"><center><?php echo $app->lang('no_data'); ?>></center></div>
    <?php endif; ?>
</div>

<script>

    $("#all_role_list").click(function(e){
        var obj = null;
        if(e.target.tagName.toLocaleUpperCase() == "INPUT"){
            obj = $(e.target);
        }

        if(obj!==null) {
            var params = {
                dtype:"json"
                ,role_id: obj.val()
            };
            params.uid = $("#uid").val();
            if (obj.prop('checked')) {
                url = "/user/save_add_role";
            } else {
                url = "/user/save_delete_role";
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
