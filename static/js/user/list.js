var forms = document.getElementsByClassName('needs-validation');
// Loop over them and prevent submission
var validation = Array.prototype.filter.call(forms, function(form) {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        event.stopPropagation();
        if (form.checkValidity() === false) {
            form.classList.add('was-validated');
            return false;
        }

        var params = {
            with_layout:0
            ,dtype:"json"
        };
        var arr = [];
        var inputs = $(form).serializeArray();
        for(var index in inputs){
            var item = inputs[index];
            if($.trim(item.value)==''){
                continue;
            }
            arr.push(item.name+"="+item.value);
        }
        url = document.location.pathname.indexOf("forbidden")==-1?'/user/list' : '/user/forbidden';
        if(arr.length>0){
            url = url+"?"+arr.join("&");
        }
        $.getMainHtml(url, params);
    }, false);
});

$("#all_user_list").click(function(e){
    var obj = null;
    if(e.target.tagName.toLocaleUpperCase() == "A"){
        obj = $(e.target);
    }
    else if(e.target.parentNode.tagName.toLocaleUpperCase() == "A"){
        obj = $(e.target.parentNode);
    }

    if(obj!==null) {
        e.preventDefault();
        url = obj.attr("href");
        nickname = obj.parents("tr").children(".nickname").text();
        uid = obj.parents("tr").attr("_uid");
        if (obj.hasClass("detail")) {
            dialogShowUserInfo(uid, nickname);
        }
        else if (obj.hasClass("reset_password")) {
            $(document).dialog({
                type: 'confirm',
                titleText: getLang("reset_password"),
                content: getLang("reset_user_password_confirm", {nickname:nickname}),
                buttonTextConfirm: getLang("reset_now"),
                buttonTextCancel: getLang("cancel"),
                onClickConfirmBtn: function(){
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {uid:uid, dtype:"json"},
                        dataType: "json",
                        success: function(res){
                            if(res.code==0){
                                $(document).dialog({
                                    contentScroll:false,
                                    titleText: getLang("password_reset_succeeded"),
                                    content: getLang("user_new_password_is", {nickname:nickname}) +
                                        "<span class='ml-2' style='font-size: 22px;color: limegreen;'>" +
                                        res.data.password+"</span>"
                                });
                            }
                            else{
                                $(document).dialog({
                                    type: "notice"
                                    , position: "bottom"
                                    , dialogClass: "dialog_warn"
                                    , infoText: res.msg
                                    , autoClose: 3000
                                    , overlayShow: false
                                });
                            }
                        }
                    });
                }
            });
        }
        else if (obj.hasClass("block_user")) {
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
        else if (obj.hasClass("unblock_user")) {
            $(document).dialog({
                type: 'confirm',
                titleText: getLang("enable_account"),
                content: getLang("enable_account_confirm"),
                buttonTextConfirm: getLang("enable_account"),
                buttonTextCancel: getLang("cancel"),
                onClickConfirmBtn: function(){
                    changeUserStatus(uid, nickname, 1);
                }
            });
        }
    }
});

$("#clear_form").click(function(e){
    $("#clear_form").parents("form").find("input").val("");
    $("#clear_form").parents("form").find("select[name=gender]").val("");
});

$('.show_title').tooltip({
    placement: 'top',
    viewport: {
        selector: '.container-viewport',
        padding: 2
    }
});
