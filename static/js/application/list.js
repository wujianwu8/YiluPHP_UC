function changeApplicationStatus(obj){
    var params = {
        dtype:"json"
        ,app_id:obj.parents("tr").attr("_id")
    };
    params[obj.attr("name")] = obj.val();
    var toast = loading();
    $.ajax({
            type: 'post'
            , dataType: 'json'
            , url: "/application/save_edit"
            , data: params
            , success: function (data, textStatus, jqXHR) {
                toast.close();
                if (data.code == 0) {
                    obj.attr("last_value", obj.val());
                    if (params.status==0){
                        obj.parents("tr").addClass("text-gray");
                    }
                    else{
                        obj.parents("tr").removeClass("text-gray");
                    }

                    $(document).dialog({
                        type: "notice"
                        , position: "bottom"
                        , infoText: getLang("save_successfully")
                        , autoClose: 2000
                        , overlayShow: false
                    });
                }
                else {
                    obj.val(obj.attr("last_value"));
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
                obj.val(obj.attr("last_value"));
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

$("#all_application_list").find("select").change(function(){
    changeApplicationStatus($(this));
});

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
        url = "/complaint/list";
        if(arr.length>0){
            url = url+"?"+arr.join("&");
        }
        $.getMainHtml(url, params);
    }, false);
});

$("#all_application_list").click(function(e){
    var obj = null;
    if(e.target.tagName.toLocaleUpperCase() == "A"){
        obj = $(e.target);
    }
    else if(e.target.parentNode.tagName.toLocaleUpperCase() == "A"){
        obj = $(e.target.parentNode);
    }

    if(obj!==null) {
        if (obj.hasClass("user_detail")) {
            e.preventDefault();
            uid = obj.attr("uid");
            nickname = obj.text();
            dialogShowUserInfo(uid, nickname);
        }
        else if (obj.hasClass("show_secret")) {
            e.preventDefault();
            app_id = obj.parents("tr").attr("_id");
            url = obj.attr("href");
            var inputDialog = $(document).dialog({
                type: "confirm"
                ,titleText: getLang("view_app_secret")
                ,content: '<div>'+getLang("display_app_secret_notice")+'</div>' +
                    '<div><input type="password" id="password" class="form-control mt-2" placeholder="'+getLang("login_password")+'"></div>'
                ,contentScroll: false
                ,buttonTextCancel: getLang("cancel")
                ,buttonTextConfirm: getLang("display")
                ,onClickConfirmBtn: function(){
                    password = $("#password").val();
                    if (!is_password(password)){
                        $(document).dialog({
                            type: "notice"
                            , position: "bottom"
                            , dialogClass: "dialog_warn"
                            , infoText: getLang("password_error")
                            , autoClose: 3000
                            , overlayShow: false
                        });
                        return false;
                    }
                    params = {
                        dtype:"json",
                        app_id:app_id,
                        password:$("#password").val()
                    };
                    params = rsaEncryptData(params, ["password"]);
                    var toast = loading();
                    $.ajax({
                            type: 'post'
                            , dataType: 'json'
                            , url: url
                            , data: params
                            , success: function (data, textStatus, jqXHR) {
                                toast.close();
                                if (data.code == 0) {
                                    inputDialog.close();
                                    $(document).dialog({
                                        titleShow: false
                                        ,content: data.data.app_secret
                                        ,contentScroll: false
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
                    return false;
                }
            });
        }
        else if (obj.hasClass("refresh_secret")) {
            e.preventDefault();
            app_id = obj.parents("tr").attr("_id");
            url = obj.attr("href");
            var inputDialog = $(document).dialog({
                type: "confirm"
                ,titleText: getLang("regenerate_secret")
                ,content: '<div>'+getLang("regenerate_secret_confirm")+'</div>' +
                    '<div><input type="password" id="password" class="form-control mt-2" placeholder="'+getLang("login_password")+'"></div>'
                ,contentScroll: false
                ,buttonTextCancel: getLang("cancel")
                ,buttonTextConfirm: getLang("regenerate_secret")
                ,onClickConfirmBtn: function(){
                    password = $("#password").val();
                    if (!is_password(password)){
                        $(document).dialog({
                            type: "notice"
                            , position: "bottom"
                            , dialogClass: "dialog_warn"
                            , infoText: getLang("password_error")
                            , autoClose: 3000
                            , overlayShow: false
                        });
                        return false;
                    }
                    params = {
                        dtype:"json",
                        app_id:app_id,
                        password:$("#password").val()
                    };
                    params = rsaEncryptData(params, ["password"]);
                    var toast = loading();
                    $.ajax({
                            type: 'post'
                            , dataType: 'json'
                            , url: url
                            , data: params
                            , success: function (data, textStatus, jqXHR) {
                                toast.close();
                                if (data.code == 0) {
                                    inputDialog.close();
                                    $(document).dialog({
                                        titleShow: false
                                        ,content: data.data.app_secret
                                        ,contentScroll: false
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
                    return false;
                }
            });
        }
        else if (obj.hasClass("delete")) {
            e.preventDefault();
            app_id = obj.parents("tr").attr("_id");
            url = obj.attr("href");
            $(document).dialog({
                type : 'confirm',
                closeBtnShow: true,
                titleText: getLang("notice"),
                buttonTextConfirm: getLang("delete_now"),
                buttonTextCancel: getLang("cancel"),
                content: getLang("delete_application_confirm",{app_name:$(obj.parents("tr").find("td")[1]).text()}),
                onClickConfirmBtn: function(){
                    var params = {
                        dtype:"json"
                        ,app_id: app_id
                    };
                    var toast = loading();
                    $.ajax({
                        type: 'post'
                        , dataType: 'json'
                        , url: url
                        , data: params
                        , success: function (data, textStatus, jqXHR) {
                            toast.close();
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