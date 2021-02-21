<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('manage_permission'),
];
?>

<h4>
    <?php echo YiluPHP::I()->lang('permission_of_application', ['app_name'=>$application_info['app_name']]); ?>
    <a class="btn btn-sm btn-outline-primary ml-4 ajax_main_content" href="/application/add_permission/<?php echo $application_info['app_id']; ?>">
        <i class="fa fa-plus" aria-hidden="true"></i>
        <?php echo YiluPHP::I()->lang('lang_add_permission'); ?>
    </a>
</h4>
<div class="table-responsive">
    <table class="table table-striped table-sm table_list" id="all_permission_list">
        <thead>
        <tr>
            <th>ID</th>
            <th><?php echo YiluPHP::I()->lang('translation'); ?></th>
            <th><?php echo YiluPHP::I()->lang('permission_name'); ?></th>
            <th><?php echo YiluPHP::I()->lang('permission_key'); ?></th>
            <th><?php echo YiluPHP::I()->lang('description'); ?></th>
            <th><?php echo YiluPHP::I()->lang('operation'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data_list as $item): ?>
        <tr _id="<?php echo $item['permission_id']; ?>">
            <td><?php echo $item['permission_id']; ?></td>
            <td><?php echo $item['permission_name_lang']; ?></td>
            <td><?php echo $item['permission_name']; ?></td>
            <td><?php echo $item['permission_key']; ?></td>
            <td class="show_title" title="<?php echo htmlspecialchars($item['description']); ?>"><?php echo mb_substr($item['description'],0,60); ?></td>
            <td>
                <a class="show_title show_users mr-2" href="/application/permission_users/<?php echo $item['permission_id']; ?>" title="<?php echo YiluPHP::I()->lang('view_people_with_this_permission'); ?>">
                    <i class="fa fa-users" aria-hidden="true"></i>
                </a>
                <?php if (empty($item['is_fixed'])){ ?>
                    <a class="ajax_main_content mr-1" href="/application/edit_permission/<?php echo $item['permission_id']; ?>">
                        <i class="fa fa-edit" aria-hidden="true"></i>
                    </a>
                    <a href="/application/delete_permission" class="delete"><i class="fa fa-close"></i></a>
                <?php } ?>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if(empty($data_list)): ?>
            <tr>
                <td colspan="5" class="pt-5 pb-5"><center><?php echo YiluPHP::I()->lang('no_data'); ?></center></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    $("#all_permission_list").click(function(e){
        var obj = null;
        if(e.target.tagName.toLocaleUpperCase() == "A"){
            obj = $(e.target);
        }
        else if(e.target.parentNode.tagName.toLocaleUpperCase() == "A"){
            obj = $(e.target.parentNode);
        }

        if(obj!==null) {
            permission_id = obj.parents("tr").attr("_id");
            url = obj.attr("href");
            permission_name = $(obj.parents("tr").find("td")[1]).text();
            if (obj.hasClass("show_users")) {
                e.preventDefault();
                var toast = loading();
                $.ajax({
                        type: 'get'
                        , dataType: 'html'
                        , url: url
                        , success: function (data, textStatus, jqXHR) {
                            toast.close();
                            $(document).dialog({
                                titleText: getLang("users_with_the_permission", {permission_name:permission_name})
                                ,content: data
                                // ,contentScroll: false
                            });
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
            }
            else if (obj.hasClass("delete")) {
                e.preventDefault();
                $(document).dialog({
                    type : 'confirm',
                    closeBtnShow: true,
                    titleText: getLang("notice"),
                    buttonTextConfirm: getLang("delete_now"),
                    buttonTextCancel: getLang("cancel"),
                    content: getLang("delete_permission_confirm", {permission_name:permission_name}),
                    onClickConfirmBtn: function(){
                        var params = {
                            dtype:"json"
                            ,permission_id: permission_id
                        };
                        var toast = loading();
                        $.ajax({
                            type: 'post'
                            , dataType: 'json'
                            , url: url
                            , data: params
                            , success: function (data, textStatus, jqXHR) {
                                toast.close();
                                if (data.code == 0) {
                                    $(document).dialog({
                                        type: "notice"
                                        , position: "bottom"
                                        , infoText: getLang("delete_successful")
                                        , autoClose: 2000
                                        , overlayShow: false
                                    });
                                    reloadPage();
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
                        });
                    }
                });
            }
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
