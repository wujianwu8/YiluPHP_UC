<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => $app->lang('menu_language_pack'),
];
?>
<style>
    #all_role_list a{
        display: inline-flex;
    }
    #all_role_list a.write_lang,
    #all_role_list a.pull_lang{
        border: #2e9eed 1px solid;
        border-radius: 2px;
        padding: 4px 1px 0 1px;
        font-size: 10px;
    }
    #all_role_list a.php{
        padding: 4px 0 0 0;
    }
    #all_role_list a.php sub{
        margin-left: -2px;
    }
    #all_role_list a.write_lang:hover,
    #all_role_list a.pull_lang:hover{
        text-decoration: none;
        background: #2e9eed;
        color: #FFFFFF;
    }
</style>

<form class="needs-validation" novalidate>
    <div class="row mb-2">
        <select class="ml-4 mb-2" name="page_size" onchange="changePage(this)">
            <option value="10">10<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="15"<?php echo $page_size=='15'? ' selected':''; ?>>15<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="20"<?php echo $page_size=='20'? ' selected':''; ?>>20<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="30"<?php echo $page_size=='30'? ' selected':''; ?>>30<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="40"<?php echo $page_size=='40'? ' selected':''; ?>>40<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="50"<?php echo $page_size=='50'? ' selected':''; ?>>50<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="100"<?php echo $page_size=='100'? ' selected':''; ?>>100<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="200"<?php echo $page_size=='200'? ' selected':''; ?>>200<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="500"<?php echo $page_size=='500'? ' selected':''; ?>>500<?php echo $app->lang('data_number_per_page'); ?></option>
        </select>
        <a href="/language/add_project" class="btn btn-sm btn-outline-primary ml-4 mb-2 ajax_main_content">
            <i class="fa fa-plus" aria-hidden="true"></i>
            <?php echo $app->lang('add_project'); ?>
        </a>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-sm table_list" id="all_role_list">
        <thead>
        <tr>
            <th>ID</th>
            <th><?php echo $app->lang('project_key'); ?></th>
            <th><?php echo $app->lang('translation'); ?></th>
            <th><?php echo $app->lang('project_name'); ?></th>
            <th><?php echo $app->lang('description'); ?></th>
            <th><?php echo $app->lang('lang_pack_storage_dir', ['type'=>'PHP']); ?></th>
            <th><?php echo $app->lang('supported_language_types'); ?></th>
            <th><?php echo $app->lang('operation'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data_list as $item): ?>
        <tr _id="<?php echo $item['id']; ?>">
            <td><?php echo $item['id']; ?></td>
            <td><?php echo $item['project_key']; ?></td>
            <td><?php echo $app->lang($item['project_name']); ?></td>
            <td><?php echo $item['project_name']; ?></td>
            <td><?php echo $item['description']; ?></td>
            <td><?php echo $item['file_dir']; ?></td>
            <td><?php echo $item['language_types']; ?></td>
            <td>
                <a class="mr-2 ajax_main_content" href="/language/table/<?php echo $item['id']; ?>">
                    <i class="fa fa-language" aria-hidden="true"></i>
                </a>
                <a class="pull_lang mr-2 php" href="/language/pull_from_file" title="从PHP文件中拉取语言(追加和覆盖)">
                    <i class="fa fa-upload" aria-hidden="true"></i><sub>ᵖʰᵖ</sub>
                </a>
                <a class="write_lang mr-2 php" href="/language/write_to_file" title="把语言写入PHP文件(替换)">
                    <i class="fa fa-download" aria-hidden="true"></i><sub>ᵖʰᵖ</sub>
                </a>
                <a class="pull_lang mr-2" href="/language/pull_from_js_file" title="从JS文件中拉取语言(追加和覆盖)">
                    <i class="fa fa-upload" aria-hidden="true"></i><sub>ʲˢ</sub>
                </a>
                <a class="write_lang mr-2" href="/language/write_to_js_file" title="把语言写入JS文件(替换)">
                    <i class="fa fa-download" aria-hidden="true"></i><sub>ʲˢ</sub>
                </a>
                <a class="ajax_main_content mr-1" href="/language/edit_project/<?php echo $item['id']; ?>">
                    <i class="fa fa-edit" aria-hidden="true"></i>
                </a>
                <a href="/language/delete_project" class="delete"><i class="fa fa-close"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if(empty($data_list)): ?>
            <tr>
                <td colspan="9" class="pt-5 pb-5"><center>没有项目</center></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php echo $app->pager->display_pages([
    'data_count' => $data_count,
    'page' => $page,
    'page_size' => $page_size,
    'a_class' => 'ajax_main_content',
    'first_page_text' => $app->lang('first_page'),
    'pre_page_text' => $app->lang('previous_page'),
    'next_page_text' => $app->lang('next_page'),
    'last_page_text' => $app->lang('last_page'),
]); ?>

<script>
    function changePage(_this) {
        var params = {
            with_layout:0
            ,dtype:"json"
        };
        url = "/language/project?page_size="+_this.value;
        $.getMainHtml(url, params);
    }

    $("#all_role_list").click(function(e){
        var obj = null;
        if(e.target.tagName.toLocaleUpperCase() == "A"){
            obj = $(e.target);
        }
        else if(e.target.parentNode.tagName.toLocaleUpperCase() == "A"){
            obj = $(e.target.parentNode);
        }

        if(obj!==null) {
            project_name = $(obj.parents("tr").find("td")[2]).text();
            url = obj.attr("href");
            project_id = obj.parents("tr").attr("_id");
            if (obj.hasClass("delete")) {
                e.preventDefault();
                $(document).dialog({
                    type : 'confirm',
                    closeBtnShow: true,
                    buttonTextConfirm: '立即删除',
//            buttonTextCancel: '取消',
                    content: '你确定要删除语言包项目"'+ project_name +'"吗?',
                    onClickConfirmBtn: function(){
                        var params = {
                            dtype:"json"
                            ,id: project_id
                        };
                        ajaxPost(url, params, function (data) {
                            $(document).dialog({
                                type: "notice"
                                , position: "bottom"
                                , infoText: "删除成功"
                                , autoClose: 2000
                                , overlayShow: false
                            });
                            obj.parents("tr").remove();
                        });
                    }
                });
            }
            else if (obj.hasClass("pull_lang") || obj.hasClass("write_lang")) {
                e.preventDefault();
                var params = {
                    dtype:"json"
                    ,project_id: project_id
                };
                ajaxPost(url, params, function (data) {
                    $(document).dialog({
                        type: "notice"
                        , position: "bottom"
                        , infoText: data.msg
                        , autoClose: 3000
                        , overlayShow: false
                    });
                });
            }
        }
    });

    $("#clear_form").click(function(e){
        $("#clear_form").parents("form").find("input").val("");
        $("#clear_form").parents("form").find("select[name=status]").val("");
    });

    $('.show_title').tooltip({
        placement: 'top',
        viewport: {
            selector: '.container-viewport',
            padding: 2
        }
    })

</script>
