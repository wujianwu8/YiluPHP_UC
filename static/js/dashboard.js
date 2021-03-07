
//记录请求当前页局部内部的请求参数
$.currentPageRequest = null;

function reloadPage()
{
    if($.currentPageRequest === null){
        document.location.reload();
    }
    else{
        $.getMainHtml($.currentPageRequest.url, $.currentPageRequest.data, $.currentPageRequest.callback);
    }
}

$.getMainHtml = function (url, data, callback, targetJquery, overlayShow, widgetDom) {
    $(".tooltip").remove();
    $.currentPageRequest = {
        url:url
        ,data:data
        ,callback:callback
    };
    if(getUrlHost(url)!=getCurrentHost()){
        document.location.href = url;
        return;
    }
    var toast = loading(overlayShow===false?false:true);
    $.getJSON(url, data, function (res) {
        toast.close();
        if (res.code==0) {
            if (typeof widgetDom == "object") {
                widgetDom.html(res.data.html);
            }
            else{
                $("main").html(res.data.html);
            }
            if (typeof targetJquery == "object") {
                if (targetJquery.parents("div[id=topMenus]").length > 0 || targetJquery.parents("div[id=leftMenus]").length > 0) {
                    $("#topMenus").find("a").removeClass("active");
                    $("#leftMenus").find("a").removeClass("active");
                    targetJquery.addClass("active");
                }
            }
            if (res.data.head_info && res.data.head_info.title) {
                document.title = res.data.head_info.title;
            }
            if (typeof callback == "function") {
                callback(res);
            }

            if (url.substring(0, 1) == "/") {
                completeUrl = getCurrentHost() + url;
            } else if (url.substring(0, 4) == "http") {
                completeUrl = url;
            } else {
                completeUrl = getUrlDirname(document.location.href) + "/" + url;
            }
            if (currentHref !== completeUrl) {
                history.pushState(null, null, url);
            }
            currentHref = document.location.href;
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
    });

};


var currentHref = document.location.href;
$(document.body).on("click", function (e) {
    linkDom = null;
    if ($(e.target).hasClass("ajax_main_content")){
        linkDom = $(e.target);
    }
    else if ($(e.target).parents(".ajax_main_content").length>0){
        linkDom = $(e.target).parents(".ajax_main_content");
    }
    if (linkDom!=null){
        e.preventDefault();
        url = linkDom.attr("href");
        if (url.indexOf("javascript")!=0) {
            $.getMainHtml(url, {with_layout: 0, dtype: 'json'}, function () {
            }, linkDom);
        }
    }
});

window.addEventListener('popstate', function(event) {
    //event.srcElement.location.pathname
    //   console.log("popstate event", event.srcElement.location.href, event);
    //   $.each (event.path, function (index, item) {
    //       console.log(item.url);
    //   })
    if (currentHref == document.location.href || currentHref+"#" == document.location.href || currentHref == document.location.href+"#"){
        return;
    }
    currentHref = document.location.href;
    $.getMainHtml(document.location.href, {with_layout:0,dtype:'json'});
});

$(".dropdown").bind("mouseover", function (e) {
    $(this).addClass("mousemove");
    if (!$(this).hasClass("show")){
        $(this).children("a").click();
    }
    $(this).children("a").blur();
});
$(".dropdown").bind("mousemove", function (e) {
    $(this).addClass("mousemove");
});
$(".dropdown").bind("mouseout", function (e) {
    var _this = $(this);
    _this.removeClass("mousemove");
    setTimeout(function () {
        if (_this.hasClass("show") && !_this.hasClass("mousemove")){
            _this.children("a").click();
        }
    }, 200);
});
$("#left_menu_btn").bind("click", function (e) {
    $("#left_menu_btn").addClass("hide_left_menu_btn");
    $(".left_sidebar_menu").addClass("show_left_menu_in_min");
});
$(".left_sidebar_menu").bind("click", function (e) {
    $("#left_menu_btn").removeClass("hide_left_menu_btn");
    $(".left_sidebar_menu").removeClass("show_left_menu_in_min");
});

$("#clear_form").click(function (e) {
    $("#clear_form").parents("form").find("input").val("");
    $("#clear_form").parents("form").find("select[name=status]").val("");
});