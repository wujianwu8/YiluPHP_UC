
var diffTime = Math.round(Date.parse(new Date())/1000) - serverTime;
function submitLoginForm(_this){
    // if (!$("#agree_agreement:checked").val()){
    //     $(document).dialog({
    //         type: "notice"
    //         ,position: "bottom"
    //         ,dialogClass:"dialog_warn"
    //         ,infoText: getLang("请阅读并同意《服务协议》和《隐私声明》。")
    //         ,autoClose: 3000
    //         ,overlayShow: false
    //     });
    //     return false;
    // }

    var params = {
        dtype:"json"
    };
    var inputs = $(_this).serializeArray();
    for(var index in inputs){
        item = inputs[index];
//            console.log(index, item);
        params[item.name] = item.value;
        switch (item.name){
            case "identity":
                if($.trim(item.value)==""){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("please_input_your_login_account")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                if(!item.value.match(/^[\w\-_\.@~!#$%^&*\(\)<>]{2,100}$/)){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("login_account_does_not_exist")
                        ,autoClose: 3000
                        ,overlayShow: false
                    });
                    return false;
                }
                if(item.value.search("@")>0 && !item.value.match(/^[\w\-_\.]+@[\w\-_]+\.[\w\-_\.]{2,30}$/)){
                    $(document).dialog({
                        type: "notice"
                        ,position: "bottom"
                        ,dialogClass:"dialog_warn"
                        ,infoText: getLang("login_account_does_not_exist")
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
                        ,infoText: getLang("please_input_your_password")
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
                        ,infoText: getLang("password_error")
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
    password = {
        time: Math.round(Date.parse(new Date())/1000)-diffTime,
        data: params.password
    };
    params.password = JSON.stringify(password);
    params = rsaEncryptData(params, ["identity","password"]);
    if(params.remember_me !== undefined && (params.remember_me=="on"||params.remember_me=="ON")){
        params.remember_me = 1;
    }
    else{
        params.remember_me = 0;
    }
    params.redirect_uri = getReturnUrl();
    var load_dialog = loading();
    $.ajax({
            type: 'post'
            , dataType: 'json'
            , url: url_pre_lang+"/sign/login"
            , data: params
            , success: function (data, textStatus, jqXHR) {
                load_dialog.close();
            console.log(data.data);
                if (data.code == 0) {
                    $(document).dialog({
                        type: "notice"
                        , position: "bottom"
                        , dialogClass: "dialog_blue"
                        , infoText: getLang("login_succeed_jumping_page")
                        , autoClose: 3000
                        , overlayShow: false
                    });
                    jumpNow(data.data.redirect_uri);
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
                load_dialog.close();
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
    checkIdentityType();
});