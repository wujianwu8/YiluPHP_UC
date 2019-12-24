
<style>
    #user_detail .col-sm-3{
        text-align: right;
        color: #aaaaaa;
    }
    #user_detail .col-sm-4{
        text-align: right;
    }
    #user_detail .col-sm-9{
        text-align: left;
    }
    #user_detail .text-gray{
        color: #cccccc;
        font-size: 12px;
    }
    @media (max-width:576px){
        #user_detail .col-sm-4,
        #user_detail .col-sm-3{
            text-align: left;
        }
    }
</style>

<div id="user_detail">
    <div class="row mb-2">
        <div class="col-sm-3 pr-2">

        </div>
        <div class="col-sm-9">
            <img src="<?php echo $user_info['avatar']; ?>" width="60" height="60">
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 pr-2">
            <?php echo $app->lang('user_id'); ?>:
        </div>
        <div class="col-sm-9">
            <?php echo $user_info['uid']; ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 pr-2">
            <?php echo $app->lang('nickname'); ?>:
        </div>
        <div class="col-sm-9">
            <?php echo $user_info['nickname']; ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 pr-2">
            <?php echo $app->lang('gender'); ?>:
        </div>
        <div class="col-sm-9">
            <?php echo $app->lang('gender_'.$user_info['gender']); ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 pr-2">
            <?php echo $app->lang('birthday'); ?>:
        </div>
        <div class="col-sm-9">
            <?php echo $user_info['birthday']; ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 pr-2">
            <?php echo $app->lang('status'); ?>:
        </div>
        <div class="col-sm-9">
            <?php echo $app->lang('user_status_'.$user_info['status']); ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 pr-2">
            <?php echo $app->lang('geo_position'); ?>:
        </div>
        <div class="col-sm-9">
            <?php echo $app->lang($user_info['country']); ?>
            <?php echo $user_info['province']; ?>
            <?php echo $user_info['city']; ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 pr-2">
            <?php echo $app->lang('sign_up_time'); ?>:
        </div>
        <div class="col-sm-9">
            <?php echo date('Y-m-d H:i:s', $user_info['ctime']); ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 pr-2">
            <?php echo $app->lang('last_active_time'); ?>:
        </div>
        <div class="col-sm-9">
            <?php echo date('Y-m-d H:i:s', $user_info['last_active']); ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 pr-2">
            <?php echo $app->lang('login_account'); ?>:
        </div>
        <div class="col-sm-9">
            <?php foreach($user_identity as $item): ?>
                <div class="row">
                    <div class="col-sm-4 pr-2">
                        <?php echo $app->lang('user_identity_type_'.$item['type']); ?>
                    </div>
                    <div class="col-sm-8">
                        <?php echo $item['identity']; ?><br>
                        <span class="text-gray"><?php echo date('Y-m-d H:i:s', $item['ctime']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 pr-2">
            <?php echo $app->lang('complain_others'); ?>:
        </div>
        <div class="col-sm-9">
            <?php echo $app->lang('numeral_time_s', ['numeral'=>['value'=>$complaint_count]]); ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 pr-2">
            <?php echo $app->lang('be_complained_by_others'); ?>:
        </div>
        <div class="col-sm-9">
            <?php echo $app->lang('numeral_time_s', ['numeral'=>['value'=>$respondent_count]]); ?>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-sm-3 pr-2"></div>
        <div class="col-sm-9">
            <input type="button" class="btn btn-primary btn-sm pl-5 pr-5 pt-2 pb-2"
                   id="user_detail_btn_change_status" status="<?php echo $user_info['status']; ?>"
                   value="<?php echo $user_info['status']?$app->lang('block_account'):$app->lang('enable_account'); ?>" >
        </div>
    </div>
</div>

<script>
    $("#user_detail_btn_change_status").click(function(e){
        nickname = "<?php echo $user_info['nickname']; ?>";
        uid = "<?php echo $user_info['uid']; ?>";
        if($(this).attr("status")=="1"){
            $(document).dialog({
                type: 'confirm',
                titleText: getLang("block_account"),
                content: getLang("block_account_confirm", {nickname:nickname}),
                buttonTextConfirm: getLang("block_account"),
                buttonTextCancel: getLang("cancel"),
                onClickConfirmBtn: function(){
                    changeUserStatus(uid, nickname, 0);
                }
            });
        }
        else{
            $(document).dialog({
                type: 'confirm',
                titleText: getLang("enable_account"),
                content: getLang("enable_account_confirm", {nickname:nickname}),
                buttonTextConfirm: getLang("enable_account"),
                buttonTextCancel: getLang("cancel"),
                onClickConfirmBtn: function(){
                    changeUserStatus(uid, nickname, 1);
                }
            });
        }
    });
</script>