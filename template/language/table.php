<!--{use_layout layout/main}-->

<?php if (empty($_REQUEST['pager_ignore_just_table'])){ ?>
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('lang_pack_for_project', ['project_name'=>YiluPHP::I()->lang($project_info['project_name'])]),
];
?>

<style>
    #all_lang_value_list .links{
        width: 120px;
    }
    #all_lang_value_list .language_value{
        min-width: 300px;
        background: none;
    }
    #all_lang_value_list .language_value:hover{
        background: #FFFFFF;
        box-shadow: inset 0px 0px 5px darkgray;
    }
    .custom-control{
        display: inline-block;
        padding-left: 1.1rem;
        margin-left: 6px;
    }
</style>

<form class="needs-validation" novalidate>
    <div class="row">
        <div class="col-md-5 mb-2">
            <input type="text" class="form-control" id="keyword" name="keyword" placeholder="<?php echo YiluPHP::I()->lang('search_keyword'); ?>" value="<?php echo isset($_REQUEST['keyword'])? $_REQUEST['keyword']:''; ?>">
        </div>
        <div class="col-md-7 mb-2">
            <button class="btn btn-primary btn-sm pl-5 pr-5 mb-2" type="submit"><?php echo YiluPHP::I()->lang('search'); ?></button>
            <button class="btn btn-primary btn-sm ml-4 mb-2" type="button" id="clear_form"><?php echo YiluPHP::I()->lang('clean_up'); ?></button>
            <select class="ml-4 mb-2" id="page_size" name="page_size">
                <option value="10">10<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
                <option value="15"<?php echo $page_size=='15'? ' selected':''; ?>>15<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
                <option value="20"<?php echo $page_size=='20'? ' selected':''; ?>>20<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
                <option value="30"<?php echo $page_size=='30'? ' selected':''; ?>>30<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
                <option value="40"<?php echo $page_size=='40'? ' selected':''; ?>>40<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
                <option value="50"<?php echo $page_size=='50'? ' selected':''; ?>>50<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
                <option value="100"<?php echo $page_size=='100'? ' selected':''; ?>>100<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
                <option value="200"<?php echo $page_size=='200'? ' selected':''; ?>>200<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
                <option value="500"<?php echo $page_size=='500'? ' selected':''; ?>>500<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
            </select>
        </div>
    </div>
</form>

<div id="all_lang_value_list">
    <?php } ?>
<div class="table-responsive">
    <input type="hidden" id="project_id" value="<?php echo $project_info['id']; ?>">
    <table class="table table-striped table-sm table_list needs-validation">
        <thead>
        <tr>
            <th></th>
            <th><?php echo YiluPHP::I()->lang('language_key_name'); ?></th>
            <?php foreach ($project_info['language_types'] as $lang){ ?>
            <th><?php echo $lang; ?></th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data_list as $language_key => $item): ?>
        <tr _language_key="<?php echo $language_key; ?>">
            <td>
                <div class="links">
                    <a href="/language/delete_lang_key" class="delete mr-2"><i class="fa fa-close"></i></a>
                    <a class="insert_key" href="/language/check_language_key_usable">
                        <i class="fa fa-indent" aria-hidden="true"></i>
                    </a>
                    <span class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="output_type_php_<?php echo $language_key; ?>"
                            <?php echo in_array('PHP', $item['output_type'])?'checked':''; ?> value="PHP">
                        <label class="custom-control-label" for="output_type_php_<?php echo $language_key; ?>">
                            <sub>ᵖʰᵖ</sub>
                        </label>
                    </span>
                    <span class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="output_type_js_<?php echo $language_key; ?>"
                            <?php echo in_array('JS', $item['output_type'])?'checked':''; ?> value="JS">
                        <label class="custom-control-label" for="output_type_js_<?php echo $language_key; ?>">
                            <sub>ʲˢ</sub>
                        </label>
                    </span>
                </div>
            </td>
            <td><?php echo $language_key; ?></td>
            <?php foreach ($project_info['language_types'] as $lang){ ?>
                <td class="language_value" contenteditable="true" lang="<?php echo $lang; ?>">
                    <?php echo empty($item[$lang])?'': htmlspecialchars($item[$lang]['language_value']); ?>
                </td>
            <?php } ?>
        </tr>
        <?php endforeach; ?>

        <?php if(empty($data_list)): ?>
            <tr>
                <td colspan="<?php echo 2+count($project_info['language_types']); ?>" class="pt-5 pb-5"><center><?php echo YiluPHP::I()->lang('no_data'); ?></center></td>
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
<?php if (empty($_REQUEST['pager_ignore_just_table'])){ ?>
</div>

<script>
    function searchLanguageValue() {
        var params = {
            with_layout:0
            ,dtype:"json"
            ,pager_ignore_just_table:1
        };
        var arr = [];
        if($.trim($("#keyword").val())!=''){
            arr.push("keyword="+$.trim($("#keyword").val()));
        }
        arr.push("page_size="+$("#page_size").val());

        url = "/language/table/"+$("#project_id").val();
        if(arr.length>0){
            url = url+"?"+arr.join("&");
        }
        $.getMainHtml(url, params, function () {
            // $("#keyword").focus().val();
            // var len = $("#keyword").val().length;
            // obj = $("#keyword")[0];
            // if (document.selection) {
            //     var sel = obj.createTextRange();
            //     sel.moveStart('character', len);
            //     sel.collapse();
            //     sel.select();
            // } else if (typeof obj.selectionStart == 'number'
            //     && typeof obj.selectionEnd == 'number') {
            //     obj.selectionStart = obj.selectionEnd = len;
            // }
        }, false, false, $("#all_lang_value_list"));
    }

    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();
            searchLanguageValue();
        }, false);
    });

    $("#all_lang_value_list").click(function(e){
        var obj = null;
        if(e.target.tagName.toLocaleUpperCase() == "A"){
            obj = $(e.target);
        }
        else if(e.target.parentNode.tagName.toLocaleUpperCase() == "A"){
            obj = $(e.target.parentNode);
        }

        if(obj!==null) {
            url = obj.attr("href");
            if (obj.hasClass("insert_key")) {
                e.preventDefault();
                var inputDialog = $(document).dialog({
                    type : 'confirm',
                    closeBtnShow: true,
                    titleText: getLang("add_new_lang_key"),
                    buttonTextCancel: getLang("cancel"),
                    buttonTextConfirm: getLang("create_now"),
                    contentScroll:false,
                    content: '<div><input id="new_language_key" class="form-control mt-2" placeholder="' +getLang("enter_a_new_lang_key")+'"></div>',
                    onClickConfirmBtn: function(){
                        new_language_key = $("#new_language_key").val();
                        var params = {
                            dtype:"json"
                            ,project_id: $("#project_id").val()
                            ,language_key: new_language_key
                        };
                        ajaxPost(url, params, function (data) {
                            if (data.code == 0) {
                                inputDialog.close();
                                tr = obj.parents("tr").clone().attr("_language_key",new_language_key);
                                tr.find("td:eq(1)").html(new_language_key);
                                for (i=2; i<tr.find("td").length; i++){
                                    tr.find("td:eq("+i+")").html("");
                                }
                                tr.find("td:eq(0)").find("input:eq(0)").prop("checked", true).attr("id", "output_type_php_"+new_language_key);
                                tr.find("td:eq(0)").find("label:eq(0)").attr("for", "output_type_php_"+new_language_key);
                                tr.find("td:eq(0)").find("input:eq(1)").prop("checked", false).attr("id", "output_type_js_"+new_language_key);
                                tr.find("td:eq(0)").find("label:eq(1)").attr("for", "output_type_js_"+new_language_key);
                                tr.insertAfter(obj.parents("tr"));
                                tr.addClass("notice_change");
                            }
                        });
                        return false;
                    }
                });
            }
            else if (obj.hasClass("delete")) {
                e.preventDefault();
                language_key = obj.parents("tr").attr("_language_key");
                $(document).dialog({
                    type : 'confirm',
                    closeBtnShow: true,
                    buttonTextConfirm: getLang("delete_now"),
                    buttonTextCancel: getLang("cancel"),
                    content: getLang("delete_lang_key_confirm", {lang_key:language_key}),
                    onClickConfirmBtn: function(){
                        var params = {
                            dtype:"json"
                            ,project_id: $("#project_id").val()
                            ,language_key: language_key
                        };
                        ajaxPost(url, params, function (data) {
                            if (data.code == 0) {
                                $(document).dialog({
                                    type: "notice"
                                    , position: "bottom"
                                    , infoText: getLang("delete_successful")
                                    , autoClose: 2000
                                    , overlayShow: false
                                });
                                obj.parents("tr").remove();
                            }
                        });
                    }
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
    });

    function saveLanguageValue(obj, is_retry){
        if (obj.attr("type")=="checkbox"){
            var output_type_obj = obj.parents("tr").find("input:checked");
            var output_type = [];
            $.each(output_type_obj, function (i, v) {
                output_type.push($(v).val());
            });
            output_type = output_type.join(",");
            var params = {
                dtype:"json"
                ,project_id: $("#project_id").val()
                ,language_key: obj.parents("tr").attr("_language_key")
                ,output_type: output_type
            };
            $.ajax({
                type: 'post'
                , dataType: 'json'
                , url: "/language/save_lang_output_type"
                , data: params
                , success: function (data, textStatus, jqXHR) {
                    if (data.code != 0) {
                        obj.prop("checked", !obj.prop("checked"));
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
            return;
        }
        var timestamp = (new Date()).getTime();
        if (obj.attr("last_save")==null || obj.attr("last_save")==0){
            obj.attr("last_save", timestamp);
            var params = {
                dtype:"json"
                ,project_id: $("#project_id").val()
                ,language_key: obj.parents("tr").attr("_language_key")
                ,language_type: obj.attr("lang")
                ,language_value: obj.text()
            };
            $.ajax({
                type: 'post'
                , dataType: 'json'
                , url: "/language/save_edit_lang_value"
                , data: params
                , success: function (data, textStatus, jqXHR) {
                    obj.attr("last_save", 0);
                    if (data.code != 0) {
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
                    obj.attr("last_save", 0);
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
        else if (is_retry===true && timestamp-obj.attr("last_save")<500){
            return;
        }
        else{
            setTimeout(function (){saveLanguageValue(obj, true);}, 600);
        }
    }

    $(function(){
        $("#all_lang_value_list").bind("input",function (e) {
            saveLanguageValue($(e.target));
        });
        $("#keyword").bind("input",function (e) {
            searchLanguageValue();
        });
    });

</script>
<?php } ?>