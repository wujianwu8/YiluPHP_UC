<!--{use_layout layout/admin_main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('my_invite_register_link'),
];
?>

<h4 class="mb-3"><?php echo YiluPHP::I()->lang('my_invite_register_link'); ?></h4>
<div class="card mb-4">
    <div class="card-body">
        <div class="input-group">
            <input type="text" class="form-control" id="invite_url" readonly value="<?php echo $invite_url; ?>">
            <div class="input-group-append">
                <button class="btn btn-outline-primary" type="button" id="copy_link_btn">
                    <i class="fa fa-copy" aria-hidden="true"></i> <?php echo YiluPHP::I()->lang('copy_link'); ?>
                </button>
                <button class="btn btn-outline-secondary" type="button" id="change_code_btn">
                    <i class="fa fa-refresh" aria-hidden="true"></i> <?php echo YiluPHP::I()->lang('change_invite_code'); ?>
                </button>
            </div>
        </div>
        <small class="text-muted mt-1 d-block">
            <?php echo YiluPHP::I()->lang('invite_code'); ?>: <span id="current_code"><?php echo $my_link['invite_code']; ?></span>
        </small>
    </div>
</div>

<h4 class="mb-3"><?php echo YiluPHP::I()->lang('who_invited_me'); ?></h4>
<div class="card mb-4">
    <div class="card-body">
        <?php if(!empty($inviter_info)): ?>
            <img src="<?php echo $inviter_info['avatar']; ?>" width="24" height="24">
            <?php echo $inviter_info['nickname']; ?>
            <span class="text-muted ml-2">UID: <?php echo $inviter_info['uid']; ?></span>
        <?php else: ?>
            <?php echo YiluPHP::I()->lang('no_data'); ?>
        <?php endif; ?>
    </div>
</div>

<h4 class="mb-3"><?php echo YiluPHP::I()->lang('users_i_invited'); ?></h4>
<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
        <tr>
            <th><?php echo YiluPHP::I()->lang('avatar'); ?></th>
            <th><?php echo YiluPHP::I()->lang('nickname'); ?></th>
            <th>UID</th>
            <th><?php echo YiluPHP::I()->lang('create_time'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($invited_users as $user): ?>
        <tr>
            <td><img src="<?php echo $user['avatar']; ?>" width="18" height="18"></td>
            <td><?php echo $user['nickname']; ?></td>
            <td><?php echo $user['uid']; ?></td>
            <td><?php echo date('Y-m-d H:i:s', $user['ctime']); ?></td>
        </tr>
        <?php endforeach; ?>

        <?php if(empty($invited_users)): ?>
            <tr>
                <td colspan="4" class="pt-5 pb-5"><center><?php echo YiluPHP::I()->lang('no_data'); ?></center></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php echo pager::I()->display_pages([
    'data_count' => $invited_count,
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

        $("#copy_link_btn").click(function(){
            var url = $("#invite_url").val();
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

        $("#change_code_btn").click(function(){
            var toast = loading();
            $.ajax({
                type: 'post'
                , dataType: 'json'
                , url: "<?php echo url_pre_lang(); ?>/invitation/change_code"
                , data: {id: "<?php echo $my_link['id']; ?>", dtype: "json"}
                , success: function (data) {
                    toast.close();
                    if (data.code == 0) {
                        var newCode = data.data.invite_code;
                        $("#current_code").text(newCode);
                        var newUrl = location.origin + "<?php echo url_pre_lang(); ?>/sign/up?invite_code=" + newCode;
                        $("#invite_url").val(newUrl);
                        $(document).dialog({
                            type: "notice"
                            ,position: "bottom"
                            ,infoText: getLang("save_successfully")
                            ,autoClose: 2000
                            ,overlayShow: false
                        });
                    } else {
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
                , error: function (XMLHttpRequest, textStatus) {
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
        });
    })();
</script>
