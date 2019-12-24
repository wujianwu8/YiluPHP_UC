<style>
    #user_nickname_list a{
        line-height: 32px;
    }
    #user_nickname_list img{
        border-radius: 2px;
    }
    #user_nickname_list .btn-outline-primary{
        border: 1px solid #007bff;
    }
</style>

<div id="user_nickname_list">
    <?php foreach ($user_list as $user){ ?>
        <a href="#" class="btn btn-outline-primary pl-2 pr-3 mr-2 mb-2 detail" uid="<?php echo $user['uid']; ?>">
            <img src="<?php echo $user['avatar']; ?>" width="20" height="20">
            <?php echo $user['nickname']; ?>
        </a>
    <?php } ?>
    <?php if (empty($user_list)){ ?>
        <center class="mt-3">暂无</center>
    <?php } ?>
</div>

<script>
    $("#user_nickname_list").click(function(e){
        var obj = null;
        if(e.target.tagName.toLocaleUpperCase() == "A"){
            obj = $(e.target);
        }
        else if(e.target.parentNode.tagName.toLocaleUpperCase() == "A"){
            obj = $(e.target.parentNode);
        }

        if(obj!==null) {
            e.preventDefault();
            uid = obj.attr("uid");
            nickname = obj.text();
            dialogShowUserInfo(uid, nickname);
        }
    });
</script>
