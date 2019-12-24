
function submitRegisterForm(_this){
    var params = {
        dtype:"json"
    };
    var inputs = $(_this).serializeArray();
    //console.log(inputs);
    for(var index in inputs){
        item = inputs[index];
        if(item.name!="confirm_password") {
            params[item.name] = item.value;
        }
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
                if(!item.value.match(/^\d{6,11}$/)){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("wrong_mobile_number")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                break;
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
                if(!item.value.match(/^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*)(?=.*[\.\$!#@_-].*).{6,20}$/)){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("password_too_simple")
                        ,autoClose: 5000
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
                        ,infoText: getLang("confirm_login_password_please")
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
                        ,autoClose: 5000
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
                if(!item.value.match(/^\d{4}$/)){
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
       //console.log(params);
    params = rsaEncryptData(params, ["mobile","password"]);
//        console.log(params);
    $("#registerButton").addClass("btn_loading").attr("disabled", true);
    $.ajax({
            type: 'post'
            , dataType: 'json'
            , url: "/sign/create_user"
            , data: params
            , success: function (data, textStatus, jqXHR) {
                $("#registerButton").removeClass("btn_loading").removeAttr("disabled", true);
                console.log(data.code, textStatus, jqXHR);
                if (data.code == 0) {
                    msg = params.is_bind==1 ? getLang("sign_up_and_bound_account_succ") : getLang("sign_up_successful");
                    $(document).dialog({
                        type: "confirm"
                        , position: "bottom"
                        ,titleText: msg
                        ,content: getLang("click_ok_to_jump_page")
                        ,overlayShow: false
                        ,buttons: [
                            {
                                name: getLang("got_it"),
                                callback: function() {
                                    window.location.reload();
                                }
                            }
                        ]
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
                $("#registerButton").removeClass("btn_loading").removeAttr("disabled", true);
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
    $("#btn_send_sms_code").click(function(){
        var mobile = $.trim($("#mobile").val());
        if(mobile==""){
            $(document).dialog({
                type: "notice"
                ,position: "bottom"
                ,dialogClass:"dialog_warn"
                ,infoText: "请填写手机号码"
                ,autoClose: 3000
                ,overlayShow: false
            });
            return false;
        }
        if(!mobile.match(/^\d{6,11}$/)){
            $(document).dialog({
                type: "notice"
                ,position: "bottom"
                ,dialogClass:"dialog_warn"
                ,infoText: "请填写正确的手机号码"
                ,autoClose: 3000
                ,overlayShow: false
            });
            return false;
        }

        $("#btn_send_sms_code").addClass("btn_loading").attr("disabled", true);
        params = {
            area_code:$("#area_code_2").val(),
            mobile:mobile,
            dtype:"json"
        };
        if ($(this).attr("use_for")=="find_password"){
            params.use_for = "find_password";
        }
        if ($(this).attr("use_for")=="sign_up"){
            params.use_for = "sign_up";
        }
        if ($(this).attr("use_for")=="bind_account"){
            params.use_for = "bind_account";
        }
        params = rsaEncryptData(params, ["mobile"]);
        $.post(
            "/send_sms_code",
            params,
            function(data,status){
                $("#btn_send_sms_code").removeClass("btn_loading");
                console.log(data);
                if(data.code==0){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_blue"
                        ,infoText: "验证码已发送"
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    var leftTime = 30;
                    $("#btn_send_sms_code").text(leftTime);
                    var time = setInterval(function(){
                        leftTime--;
                        $("#btn_send_sms_code").text(leftTime);
                        if(leftTime<=0){
                            clearInterval(time);
                            $("#btn_send_sms_code").text("Send Code").removeAttr("disabled");
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
                    $("#btn_send_sms_code").removeAttr("disabled");
                }
            },
            "json"
        );
    });
});