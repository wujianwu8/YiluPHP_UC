
if( ("onhashchange" in window) && ((typeof document.documentMode==="undefined") || document.documentMode==8)) {
    // 浏览器支持onhashchange事件
    window.onhashchange = hashChangeFire;  // TODO，对应新的hash执行的操作函数
} else {
    // 不支持则用定时器检测的办法
    setInterval(function() {
        var ischanged = isHashChanged();  // TODO，检测hash值或其中某一段是否更改的函数
        if(ischanged) {
            hashChangeFire();  // TODO，对应新的hash执行的操作函数
        }
    }, 150);
}

function showTarget(hash){
    arr = ["by_mobile","by_email","reset_by_mobile","reset_by_email","select_method"];
    if (hash==""){
        hash = "select_method";
    }
    console.log(hash);
    hash = hash.split("&");
    console.log(hash);
    var isInArr = false;
    for(var i in hash){
        if (hash[i]==""){
            continue;
        }
        if($.inArray(hash[i], arr)>=0){
            isInArr = true;
        }
    }
    if(!isInArr){
        hash = ["select_method"];
    }
    console.log(hash);
    for(var i in arr){
        if(arr[i] == hash[0]){
//                console.log($("#"+id).length, arr[i]);
            $("#"+arr[i]).show();
            if(hash.length>1){
                $("#"+arr[i]).find(".account").html(hash[1]);
                $("#"+arr[i]).find(".hidden_input").val(hash[1]);
                tmp = $("#"+arr[i]).find(".resend_link");
                if(tmp.length>0 && tmp.attr("href").indexOf("by_mobile")>0){
                    mobile = hash[1].split("-");
                    tmp.attr("href", "#by_mobile&" + (mobile.length>1 ? mobile[1] : hash[1]));
                }
                if(tmp.length>0 && tmp.attr("href").indexOf("by_email")>0){
                    tmp.attr("href", "#by_email&" + hash[1]);
                }
            }
            if(hash.length>2){
                $("#"+arr[i]).find("input[name=verify_code]").val(hash[2]);
            }
        }
        else {
//                console.log($("#"+id).length, arr[i]);
            $("#"+arr[i]).hide();
        }
    }
}

function hashChangeFire(){
    href = document.location.href;
    href = href.split("#");
    if(href.length>1){
        showTarget(href[1]);
    }
    else{
        showTarget("");
    }
}
hashChangeFire();

function checkMobileCodeForm(_this){
    var params = {
        dtype:"json"
    };
    var inputs = $(_this).serializeArray();
//        console.log(inputs);
    for(var index in inputs){
        item = inputs[index];
//            console.log(index, item);
        params[item.name] = item.value;
        switch (item.name){
            case "mobile":
                if($.trim(item.value)==""){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("please_input_mobile_number")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                break;
            case "verify_code":
                if($.trim(item.value)==""){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("please_enter_verify_code_on_mobile")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                break;
            default:
                break;
        }
    }
    mobile = params.mobile;
    verify_code = params.verify_code;
    console.log(params);
    params = rsaEncryptData(params, ["mobile","verify_code"]);
//      console.log(params);
    $("#checkMobileCodeBtn").addClass("btn_loading").attr("disabled", true);
    $.ajax({
            type: 'post'
            , dataType: 'json'
            , url: url_pre_lang+"/sign/check_sms_code"
            , data: params
            , success: function (data, textStatus, jqXHR) {
                $("#checkMobileCodeBtn").removeClass("btn_loading").removeAttr("disabled", true);
                console.log(data.code, textStatus, jqXHR);
                if (data.code == 0) {
                    document.location.href = "#reset_by_mobile&" + params.area_code + "-" + mobile + "&" + verify_code;
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
                console.log(XMLHttpRequest, textStatus, errorThrown);
                $("#checkMobileCodeBtn").removeClass("btn_loading").removeAttr("disabled", true);
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
    return false;
}

function checkEmailCodeForm(_this){
    var params = {
        dtype:"json"
    };
    var inputs = $(_this).serializeArray();
//        console.log(inputs);
    for(var index in inputs){
        item = inputs[index];
//            console.log(index, item);
        params[item.name] = item.value;
        switch (item.name){
            case "email":
                if(!is_email(item.value)){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("email_error")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                break;
            case "verify_code":
                if($.trim(item.value)==""){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("please_enter_verify_code_on_email")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                break;
            default:
                break;
        }
    }
    email = params.email;
    verify_code = params.verify_code;
    console.log(params);
    params = rsaEncryptData(params, ["email","verify_code"]);
//      console.log(params);
    $("#checkEmailCodeBtn").addClass("btn_loading").attr("disabled", true);
    $.ajax({
            type: 'post'
            , dataType: 'json'
            , url: url_pre_lang+"/sign/check_email_code"
            , data: params
            , success: function (data, textStatus, jqXHR) {
                $("#checkEmailCodeBtn").removeClass("btn_loading").removeAttr("disabled", true);
                console.log(data.code, textStatus, jqXHR);
                if (data.code == 0) {
                    document.location.href = "#reset_by_email&" + email + "&" + verify_code;
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
                console.log(XMLHttpRequest, textStatus, errorThrown);
                $("#checkEmailCodeBtn").removeClass("btn_loading").removeAttr("disabled", true);
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
    return false;
}

function resetPasswordForm(_this){
    var params = {
        dtype:"json"
    };
    var inputs = $(_this).serializeArray();
    console.log(inputs);
    for(var index in inputs){
        item = inputs[index];
//            console.log(index, item);
        if(item.name != "confirm_password"){
            params[item.name] = item.value;
        }
        switch (item.name){
            case "password":
                if($.trim(item.value)==""){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("please_set_your_password")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                if(!is_password(item.value)){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("password_too_simple")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                break;
            case "confirm_password":
                if($.trim(item.value)==""){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("confirm_new_password")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                if(item.value!=params.password){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("re_input_password_error")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                break;
            case "verify_code":
                if($.trim(item.value)==""){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("please_input_verify_code")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                if(!item.value.match(/^[\w\d]{4,6}$/)){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("verify_code_error")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                break;
            default:
                break;
        }
    }
    console.log(params);
    if(params.mobile){
        params = rsaEncryptData(params, ["mobile","password","verify_code"]);
    }
    else if(params.email){
        params = rsaEncryptData(params, ["email","password","verify_code"]);
    }

//      console.log(params);
    btn = $(_this).find(".reset_password_btn");
    btn.addClass("btn_loading").attr("disabled", true);
    $.ajax({
            type: 'post'
            , dataType: 'json'
            , url: url_pre_lang+"/sign/reset_password"
            , data: params
            , success: function (data, textStatus, jqXHR) {
                btn.removeClass("btn_loading").removeAttr("disabled", true);
                console.log(data.code, textStatus, jqXHR);
                if (data.code == 0) {
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_blue"
                        ,infoText: getLang("set_password_successfully")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
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
                console.log(XMLHttpRequest, textStatus, errorThrown);
                btn.removeClass("btn_loading").removeAttr("disabled", true);
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
    return false;
}

$(function(){
    $("#btn_send_email_code").click(function(){
        var email = $.trim($("#email").val());
        if(email==""){
            $(document).dialog({
                type: "notice"
                ,position: "bottom"
                ,dialogClass:"dialog_warn"
                ,infoText: getLang("please_input_email")
                ,autoClose: 3000
                ,overlayShow: false
            });
            return false;
        }
        if(!is_email(email)){
            $(document).dialog({
                type: "notice"
                ,position: "bottom"
                ,dialogClass:"dialog_warn"
                ,infoText: getLang("email_error")
                ,autoClose: 3000
                ,overlayShow: false
            });
            return false;
        }

        $("#btn_send_email_code").addClass("btn_loading").attr("disabled", true);
        params = {
            email:email,
            use_for:'find_password',
            dtype:"json"
        };
        params = rsaEncryptData(params, ["email"]);
        $.post(
            url_pre_lang+"/send_email_code",
            params,
            function(data,status){
                $("#btn_send_email_code").removeClass("btn_loading");
                console.log(data);
                if(data.code==0){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_blue"
                        ,infoText: getLang("verify_code_sent")
                        ,autoClose: 30000
                        ,overlayShow: false
                    });
                    var leftTime = 30;
                    $("#btn_send_email_code").text(leftTime);
                    var time = setInterval(function(){
                        leftTime--;
                        $("#btn_send_email_code").text(leftTime);
                        if(leftTime<=0){
                            clearInterval(time);
                            $("#btn_send_email_code").text("Send Code").removeAttr("disabled");
                        }
                    },1000);
                }
                else {
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: data.msg
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    $("#btn_send_email_code").removeAttr("disabled");
                }
            },
            "json"
        );
    });
});