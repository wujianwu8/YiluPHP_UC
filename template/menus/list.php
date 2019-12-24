<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => $app->lang('menu_custom_menu'),
];
?>

<div class="table-responsive" id="edit_menus_table">
    <h2>
        <?php echo $app->lang('head_menu'); ?>
        <a href="/menus/add" class="btn btn-sm btn-outline-primary ml-4 ajax_main_content">
            <i class="fa fa-plus" aria-hidden="true"></i>
            <?php echo $app->lang('add_menu'); ?>
        </a>
    </h2>
    <table class="table table-striped table-sm table_menu_list">
        <thead>
        <tr>
            <th><?php echo $app->lang('menu_preview'); ?></th>
            <th><?php echo $app->lang('menu_name'); ?></th>
            <th><?php echo $app->lang('parent_level'); ?></th>
            <th><?php echo $app->lang('sort'); ?></th>
            <th><?php echo $app->lang('operation'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($top_menus as $item): ?>
        <tr _id="<?php echo $item['id']; ?>">
            <td>
                <?php if(!empty($item['icon']) && strpos($item['icon'], '<')===false): ?>
                    <i class="fa <?php echo $item['icon']; ?>" aria-hidden="true"></i>
                <?php endif; ?>
                <?php if(!empty($item['icon']) && strpos($item['icon'], '<')!==false): ?>
                    <?php echo $item['icon']; ?>
                <?php endif; ?>
                <?php echo $app->lang($item['lang_key']); ?>
            </td>
            <td>
                <input name="lang_key" value="<?php echo $item['lang_key']; ?>" <?php echo $item['type']=='SYSTEM'?'class="disabled" disabled':''; ?>>
            </td>
            <td>
                <?php if($item['type']!='SYSTEM'): ?>
                <select name="parent_menu" required="">
                    <option value="0"><?php echo $app->lang('top_level'); ?></option>
                    <?php
                    $position = '';
                    foreach($parent_menus as $menu){
                        ?>
                        <?php if($position!=$menu['position']){ ?>
                            <option disabled><?php echo $menu['position']=='TOP'?$app->lang('head_menu'):$app->lang('left_menu'); ?></option>
                            <?php $position=$menu['position']; } ?>
                        <option value="<?php echo $menu['id'] ?>" <?php echo $menu['id']==$item['parent_menu']?'selected':'' ?> <?php echo $menu['id']==$item['id']?'disabled':'' ?> >
                            <?php echo $app->lang($menu['lang_key']) ?>
                        </option>
                    <?php } ?>
                </select>
                <?php endif; ?>
            </td>
            <td class="weight">
                <input type="number" name="weight" value="<?php echo $item['weight']; ?>">
            </td>
            <td>
                <a href="/menus/edit/<?php echo $item['id'] ?>" class="ajax_main_content"><i class="fa fa-edit"></i></a>
                <?php if($item['type']!='SYSTEM'): ?>
                <a class="delete delete_menu"><i class="fa fa-close"></i></a>
                <?php endif; ?>
            </td>
        </tr>
            <?php if(!empty($item['children'])): ?>
                <?php foreach($item['children'] as $child): ?>
                    <tr _id="<?php echo $child['id']; ?>" class="child">
                        <td>
                            <span class="ml-5"></span>
                            <?php if(!empty($child['icon']) && strpos($child['icon'], '<')===false): ?>
                                <i class="fa <?php echo $child['icon']; ?>" aria-hidden="true"></i>
                            <?php endif; ?>
                            <?php if(!empty($child['icon']) && strpos($child['icon'], '<')!==false): ?>
                                <?php echo $child['icon']; ?>
                            <?php endif; ?>
                            <?php echo $app->lang($child['lang_key']); ?>
                        </td>
                        <td>
                            <input value="<?php echo $child['lang_key']; ?>" <?php echo $item['type']=='SYSTEM'?'class="disabled" disabled':''; ?>>
                        </td>
                        <td>
                            <?php if($child['type']!='SYSTEM'): ?>
                            <select name="parent_menu" required="">
                                <option value="0"><?php echo $app->lang('top_level'); ?></option>
                                <?php
                                $position = '';
                                foreach($parent_menus as $menu){
                                    ?>
                                    <?php if($position!=$menu['position']){ ?>
                                        <option disabled><?php echo $menu['position']=='TOP'?$app->lang('head_menu'):$app->lang('left_menu'); ?></option>
                                        <?php $position=$menu['position']; } ?>
                                    <option value="<?php echo $menu['id'] ?>" <?php echo $menu['id']==$child['parent_menu']?'selected':'' ?> <?php echo $menu['id']==$child['id']?'disabled':'' ?> >
                                        <?php echo $app->lang($menu['lang_key']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php endif; ?>
                        </td>
                        <td class="weight">
                            <input type="number" name="weight" value="<?php echo $child['weight']; ?>">
                        </td>
                        <td>
                            <a href="/menus/edit/<?php echo $child['id'] ?>" class="ajax_main_content"><i class="fa fa-edit"></i></a>
                            <?php if($child['type']!='SYSTEM'): ?>
                                <a class="delete delete_menu"><i class="fa fa-close"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>
        <?php echo $app->lang('left_menu'); ?>
        <a href="/menus/add" class="btn btn-sm btn-outline-primary ml-4 ajax_main_content">
            <i class="fa fa-plus" aria-hidden="true"></i>
            <?php echo $app->lang('add_menu'); ?>
        </a>
    </h2>
    <table class="table table-striped table-sm table_menu_list">
        <thead>
        <tr>
            <th><?php echo $app->lang('menu_preview'); ?></th>
            <th><?php echo $app->lang('menu_name'); ?></th>
            <th><?php echo $app->lang('parent_level'); ?></th>
            <th><?php echo $app->lang('sort'); ?></th>
            <th><?php echo $app->lang('operation'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($left_menus as $item): ?>
            <tr _id="<?php echo $item['id']; ?>">
                <td>
                    <?php if(!empty($item['icon']) && strpos($item['icon'], '<')===false): ?>
                        <i class="fa <?php echo $item['icon']; ?>" aria-hidden="true"></i>
                    <?php endif; ?>
                    <?php if(!empty($item['icon']) && strpos($item['icon'], '<')!==false): ?>
                        <?php echo $item['icon']; ?>
                    <?php endif; ?>
                    <?php echo $app->lang($item['lang_key']); ?>
                </td>
                <td>
                    <input name="lang_key" value="<?php echo $item['lang_key']; ?>" <?php echo $item['type']=='SYSTEM'?'class="disabled" disabled':''; ?>>
                </td>
                <td>
                    <?php if($item['type']!='SYSTEM'): ?>
                    <select name="parent_menu" required="">
                        <option value="0"><?php echo $app->lang('top_level'); ?></option>
                        <?php
                        $position = '';
                        foreach($parent_menus as $menu){
                            ?>
                            <?php if($position!=$menu['position']){ ?>
                                <option disabled><?php echo $menu['position']=='TOP'?$app->lang('head_menu'):$app->lang('left_menu'); ?></option>
                                <?php $position=$menu['position']; } ?>
                            <option value="<?php echo $menu['id'] ?>" <?php echo $menu['id']==$item['parent_menu']?'selected':'' ?> <?php echo $menu['id']==$item['id']?'disabled':'' ?> >
                                <?php echo $app->lang($menu['lang_key']) ?>
                            </option>
                        <?php } ?>
                    </select>
                    <?php endif; ?>
                </td>
                <td class="weight">
                    <input type="number" name="weight" value="<?php echo $item['weight']; ?>">
                </td>
                <td>
                    <a href="/menus/edit/<?php echo $item['id'] ?>" class="ajax_main_content"><i class="fa fa-edit"></i></a>
                    <?php if($item['type']!='SYSTEM'): ?>
                        <a class="delete delete_menu"><i class="fa fa-close"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if(!empty($item['children'])): ?>
                <?php foreach($item['children'] as $child): ?>
                    <tr _id="<?php echo $child['id']; ?>" class="child">
                        <td>
                            <span class="ml-5"></span>
                            <?php if(!empty($child['icon']) && strpos($child['icon'], '<')===false): ?>
                                <i class="fa <?php echo $child['icon']; ?>" aria-hidden="true"></i>
                            <?php endif; ?>
                            <?php if(!empty($child['icon']) && strpos($child['icon'], '<')!==false): ?>
                                <?php echo $child['icon']; ?>
                            <?php endif; ?>
                            <?php echo $app->lang($child['lang_key']); ?>
                        </td>
                        <td>
                            <input name="lang_key" value="<?php echo $child['lang_key']; ?>" <?php echo $item['type']=='SYSTEM'?'class="disabled" disabled':''; ?>>
                        </td>
                        <td>
                            <?php if($child['type']!='SYSTEM'): ?>
                            <select name="parent_menu" required="">
                                <option value="0"><?php echo $app->lang('top_level'); ?></option>
                                <?php
                                $position = '';
                                foreach($parent_menus as $menu){
                                    ?>
                                    <?php if($position!=$menu['position']){ ?>
                                        <option disabled><?php echo $menu['position']=='TOP'?$app->lang('head_menu'):$app->lang('left_menu'); ?></option>
                                        <?php $position=$menu['position']; } ?>
                                    <option value="<?php echo $menu['id'] ?>" <?php echo $menu['id']==$child['parent_menu']?'selected':'' ?> <?php echo $menu['id']==$child['id']?'disabled':'' ?> >
                                        <?php echo $app->lang($menu['lang_key']) ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <?php endif; ?>
                        </td>
                        <td class="weight">
                            <input type="number" name="weight" value="<?php echo $child['weight']; ?>">
                        </td>
                        <td>
                            <a href="/menus/edit/<?php echo $child['id'] ?>" class="ajax_main_content"><i class="fa fa-edit"></i></a>
                            <?php if($child['type']!='SYSTEM'): ?>
                                <a class="delete delete_menu"><i class="fa fa-close"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function changeMenuValue(obj){
        var params = {
            dtype:"json"
            ,id:obj.parents("tr").attr("_id")
        };
        params[obj.attr("name")] = obj.val();
        var toast = loading();
        $.ajax({
                type: 'post'
                , dataType: 'json'
                , url: "/menus/save_edit"
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
            }
        );
    }

    $("#edit_menus_table").find("input").change(function(){
        changeMenuValue($(this));
    });
    $("#edit_menus_table").find("select").change(function(){
        changeMenuValue($(this));
    });
    $("#edit_menus_table").find(".delete_menu").click(function(){
        var _id = $(this).parents("tr").attr("_id");
        $(document).dialog({
            type : 'confirm',
            closeBtnShow: true,
            buttonTextConfirm: '立即删除',
//            buttonTextCancel: '取消',
            content: '你确定要删除菜单"'+ $($(this).parents("tr").find("td")[0]).text() +'"吗?',
            onClickConfirmBtn: function(){
                var params = {
                    dtype:"json"
                    ,id: _id
                };
                var toast = loading();
                $.ajax({
                    type: 'post'
                    , dataType: 'json'
                    , url: "/menus/delete"
                    , data: params
                    , success: function (data, textStatus, jqXHR) {
                        toast.close();
                        if (data.code == 0) {
                            $(document).dialog({
                                type: "notice"
                                , position: "bottom"
                                , infoText: "删除成功"
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
    });
</script>