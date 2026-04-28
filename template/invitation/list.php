<!--{use_layout layout/admin_main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('invitation_link'),
];
?>

<form class="needs-validation" novalidate>
    <div class="row mb-2">
        <div class="col-md-2">
            <input type="text" class="form-control" name="uid" placeholder="<?php echo YiluPHP::I()->lang('inviter'); ?> UID" value="<?php echo isset($_REQUEST['uid'])? $_REQUEST['uid']:''; ?>">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="scene" placeholder="<?php echo YiluPHP::I()->lang('invite_scene'); ?>" value="<?php echo isset($_REQUEST['scene'])? $_REQUEST['scene']:''; ?>">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="invite_code" placeholder="<?php echo YiluPHP::I()->lang('invite_code'); ?>" value="<?php echo isset($_REQUEST['invite_code'])? $_REQUEST['invite_code']:''; ?>">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="remark" placeholder="<?php echo YiluPHP::I()->lang('remark'); ?>" value="<?php echo isset($_REQUEST['remark'])? $_REQUEST['remark']:''; ?>">
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
            <option value="50"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='50'? ' selected':''; ?>>50<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
            <option value="100"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='100'? ' selected':''; ?>>100<?php echo YiluPHP::I()->lang('data_number_per_page'); ?></option>
        </select>
        <a href="<?php echo url_pre_lang(); ?>/invitation/add" class="btn btn-sm btn-outline-primary ml-4 ajax_main_content">
            <i class="fa fa-plus" aria-hidden="true"></i>
            <?php echo YiluPHP::I()->lang('add_invitation_link'); ?>
        </a>
    </div>
</form>

<div class="table-responsive" <?php echo count($data_list)<2?'style="padding-bottom:30px;"':''; ?> >
    <table class="table table-striped table-sm table_list" id="all_invitation_list">
        <thead>
        <tr>
            <th>ID</th>
            <th><?php echo YiluPHP::I()->lang('owner'); ?></th>
            <th><?php echo YiluPHP::I()->lang('invite_scene'); ?></th>
            <th><?php echo YiluPHP::I()->lang('invite_code'); ?></th>
            <th><?php echo YiluPHP::I()->lang('cookie_ttl'); ?></th>
            <th><?php echo YiluPHP::I()->lang('remark'); ?></th>
            <th><?php echo YiluPHP::I()->lang('create_time'); ?></th>
            <th><?php echo YiluPHP::I()->lang('operation'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data_list as $item): ?>
        <tr>
            <td><?php echo $item['id']; ?></td>
            <td>
                <?php if(isset($user_map[$item['uid']])): ?>
                    <img src="<?php echo $user_map[$item['uid']]['avatar']; ?>" width="18" height="18">
                    <?php echo $user_map[$item['uid']]['nickname']; ?>
                <?php else: ?>
                    <?php echo $item['uid']; ?>
                <?php endif; ?>
            </td>
            <td><?php echo $item['scene']; ?></td>
            <td><?php echo $item['invite_code']; ?></td>
            <td><?php echo $item['cookie_ttl']; ?></td>
            <td><?php echo $item['remark']; ?></td>
            <td><?php echo date('Y-m-d H:i:s', $item['ctime']); ?></td>
            <td>
                <a class="ajax_main_content mr-2" href="<?php echo url_pre_lang(); ?>/invitation/edit/<?php echo $item['id']; ?>">
                    <i class="fa fa-edit" aria-hidden="true"></i>
                </a>
                <a class="copy_link mr-2" href="javascript:;" data-code="<?php echo $item['invite_code']; ?>" data-scene="<?php echo $item['scene']; ?>">
                    <i class="fa fa-copy" aria-hidden="true"></i>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if(empty($data_list)): ?>
            <tr>
                <td colspan="8" class="pt-5 pb-5"><center><?php echo YiluPHP::I()->lang('no_data'); ?></center></td>
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
    (function() {
        function copyText(text){
            if (navigator.clipboard && navigator.clipboard.writeText) {
                return navigator.clipboard.writeText(text);
            }
            return new Promise(function(resolve, reject) {
                var input = document.createElement("input");
                input.value = text;
                document.body.appendChild(input);
                input.select();
                input.setSelectionRange(0, input.value.length);
                try {
                    if (document.execCommand("copy")) {
                        resolve();
                    } else {
                        reject();
                    }
                } catch (e) {
                    reject(e);
                }
                document.body.removeChild(input);
            });
        }

        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                event.stopPropagation();
                var params = {with_layout:0, dtype:'json'};
                var inputs = $(form).serializeArray();
                for(var index in inputs){
                    if(inputs[index].value !== ''){
                        params[inputs[index].name] = inputs[index].value;
                    }
                }
                $.getMainHtml("<?php echo url_pre_lang(); ?>/invitation/list", params);
            }, false);
        });

        $("#clear_form").click(function(){
            $("form.needs-validation").find("input,select").val("");
            $.getMainHtml("<?php echo url_pre_lang(); ?>/invitation/list", {with_layout:0, dtype:'json'});
        });

        $("select[name='page_size']").change(function(){
            $("form.needs-validation").trigger("submit");
        });

        $(".copy_link").click(function(){
            var code = $(this).data("code");
            var scene = $(this).data("scene");
            var url = location.origin + "<?php echo url_pre_lang(); ?>" + (scene === "register" ? "/sign/up?invite_code=" : "/?invite_code=") + code;
            copyText(url).then(function(){
                $(document).dialog({
                    type: "notice"
                    ,position: "bottom"
                    ,infoText: getLang("copy_success")
                    ,autoClose: 2000
                    ,overlayShow: false
                });
            }).catch(function(){
                $(document).dialog({
                    type: "notice"
                    ,position: "bottom"
                    ,dialogClass:"dialog_warn"
                    ,infoText: "复制失败"
                    ,autoClose: 3000
                    ,overlayShow: false
                });
            });
        });
    })();
</script>
