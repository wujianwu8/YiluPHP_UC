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
        url = "/role/list";
        if(arr.length>0){
            url = url+"?"+arr.join("&");
        }
        $.getMainHtml(url, params);
    }, false);
});

$("#all_role_list").click(function(e){
    var obj = null;
    if(e.target.tagName.toLocaleUpperCase() == "A"){
        obj = $(e.target);
    }
    else if(e.target.parentNode.tagName.toLocaleUpperCase() == "A"){
        obj = $(e.target.parentNode);
    }

    if(obj!==null) {
        role_name = $(obj.parents("tr").find("td")[1]).text();
        url = obj.attr("href");
        if (obj.hasClass("show_users")) {
            e.preventDefault();
            var toast = loading();
            $.ajax({
                    type: 'get'
                    , dataType: 'html'
                    , url: url
                    , success: function (data, textStatus, jqXHR) {
                        toast.close();
                        $(document).dialog({
                            titleText: getLang("users_with_the_role", {role_name:role_name})
                            ,content: data
                            // ,contentScroll: false
                        });
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
        else if (obj.hasClass("delete")) {
            e.preventDefault();
            role_id = obj.parents("tr").attr("_id");
            $(document).dialog({
                type : 'confirm',
                closeBtnShow: true,
                titleText: getLang("notice"),
                buttonTextConfirm: getLang("delete_now"),
                buttonTextCancel: getLang("cancel"),
                content: getLang("delete_role_confirm"),
                onClickConfirmBtn: function(){
                    var params = {
                        dtype:"json"
                        ,role_id: role_id
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
