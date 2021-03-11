
/**
 * 文本框根据输入内容自适应高度
 * @param                {HTMLElement}        输入框元素
 * @param                {Number}                设置光标与输入框保持的距离(默认0)
 * @param                {Number}                设置最大高度(可选)
 */
function autoTextarea(elem, extra, maxHeight) {
    extra = extra || 0;
    var isFirefox = !!document.getBoxObjectFor || 'mozInnerScreenX' in window,
        isOpera = !!window.opera && !!window.opera.toString().indexOf('Opera'),
        addEvent = function(type, callback) {
            elem.addEventListener ?
                elem.addEventListener(type, callback, false) :
                elem.attachEvent('on' + type, callback);
        },
        getStyle = elem.currentStyle ? function(name) {
            var val = elem.currentStyle[name];
            if (name === 'height' && val.search(/px/i) !== 1) {
                var rect = elem.getBoundingClientRect();
                return rect.bottom - rect.top -
                    parseFloat(getStyle('paddingTop')) -
                    parseFloat(getStyle('paddingBottom')) + 'px';
            };
            return val;
        } : function(name) {
            return getComputedStyle(elem, null)[name];
        },
        minHeight = parseFloat(getStyle('height'));
    elem.style.resize = 'none';
    var change = function() {
        var scrollTop, height,
            padding = 0,
            style = elem.style;
        if (elem._length === elem.value.length) return;
        elem._length = elem.value.length;
        if (!isFirefox && !isOpera) {
            padding = parseInt(getStyle('paddingTop')) + parseInt(getStyle('paddingBottom'));
        };
        scrollTop = document.body.scrollTop || document.documentElement.scrollTop;
        elem.style.height = minHeight + 'px';
        if (elem.scrollHeight > minHeight) {
            if (maxHeight && elem.scrollHeight > maxHeight) {
                height = maxHeight - padding;
                style.overflowY = 'auto';
            } else {
                height = elem.scrollHeight - padding;
                style.overflowY = 'hidden';
            };
            style.height = height + extra + 'px';
            scrollTop += parseInt(style.height) - elem.currHeight;
            document.body.scrollTop = scrollTop;
            document.documentElement.scrollTop = scrollTop;
            elem.currHeight = parseInt(style.height);
        };
    };
    addEvent('propertychange', change);
    addEvent('input', change);
    addEvent('focus', change);
    change();
};

function getCurrentHost()
{
    var url = document.location.href;
    var arrUrl = url.split("//");
    var start = arrUrl[1].indexOf("/");
    var host = arrUrl[1].substring(0,start);
    return arrUrl[0]+"//"+host;
}
function getUrlHost(url)
{
    var arrUrl = url.split("//");
    if (arrUrl.length<2){
        url = document.location.href;
        arrUrl = url.split("//");
    }
    var start = arrUrl[1].indexOf("/");
    var host = arrUrl[1].substring(0,start);
    return arrUrl[0]+"//"+host;
}
function getUrlDirname(url)
{
    tmp = url.split("?");
    tmp = tmp[0];
    tmp = tmp.split("#");
    tmp = tmp[0];
    tmp = tmp.split("/");
    completeUrl = [];
    for (i=0; i<tmp.length-1; i++){
        completeUrl.push(tmp[i]);
    }
    completeUrl = completeUrl.join("/");
    return completeUrl;
}

function loading(overlayShow){
    if (overlayShow!==false){
        overlayShow=true;
    }
    return $(document).dialog({
        type : 'notice'
        ,dialogClass:"dialog_transparent"
        ,content: '<div class="loadEffect"><span></span><span></span>' +
            '<span></span><span></span><span>' +
            '</span><span></span><span>' +
            '</span><span></span></div>'
        , overlayShow: overlayShow
    });
}

function dialogShowUserInfo(uid, nickname) {
    $.get("/user/detail/"+uid, function(data){
        $(document).dialog({
            titleText: getLang("details_of_user", {nickname:nickname}),
            content: data,
            buttonTextConfirm: getLang("close")
        });
    });
}

function changeUserStatus(uid, nickname, status){
    $.ajax({
        type: "POST",
        url: "/user/change_user_status",
        data: {uid:uid, status:status, dtype:"json"},
        dataType: "json",
        success: function(res){
            if(res.code==0){
                reloadPage();
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

/*
* 使用RSA方法加密某个对象中的指定值
 * @param object objData 包含键值对的对象
 * @param array field 需要加密的键名
* */
function rsaEncryptData(objData, field){
    var encrypt = new JSEncrypt();
    encrypt.setPublicKey(rsaPublicKey);
    for(var i in field){
        if(objData[field[i]]!==undefined && objData[field[i]]!==null){
            objData[field[i]] = encrypt.encrypt(objData[field[i]]);
            objData[field[i]] = encodeURI(objData[field[i]]).replace(/\+/g, '%2B');
        }
    }
    return objData;
}

function is_email(email) {
    return email.match(/^[a-zA-Z0-9]+([-_.][a-zA-Z0-9]+)*@([a-zA-Z0-9]+[-.])+([a-z]{2,10})$/);
}

function is_password(password) {
    return password.match(/^(?=.*[0-9].*)(?=.*[A-Z].*)(?=.*[a-z].*)(?=.*[\.\$!#@_-].*).{6,20}$/);
}

function is_key_string(string) {
    return string.match(/^[a-zA-Z0-9_]+$/);
}

function ajaxPost(url, params, callback, loadingOverlayShow)
{
    var toast = loading(loadingOverlayShow);
    $.ajax({
            type: 'post'
            , dataType: 'json'
            , url: url
            , data: params
            , success: function (data, textStatus, jqXHR) {
                toast.close();
                if (data.code == 0) {
                    callback(data);
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
}

function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
    var r = window.location.search.substr(1).match(reg);  //匹配目标参数
    if (r != null) return unescape(r[2]); return null; //返回参数值
}
function getUrlParms(){
    var args=new Object();
    var query=location.search.substring(1);//获取查询串
    var pairs=query.split("&");//在逗号处断开
    for(var i=0;i<pairs.length;i++){
        var pos=pairs[i].indexOf('=');//查找name=value
        if(pos==-1) continue;//如果没有找到就跳过
        var argname=pairs[i].substring(0,pos);//提取name
        var value=pairs[i].substring(pos+1);//提取value
        args[argname]=decodeURIComponent(value);//存为属性
    }
    return args;
}

function parseUrlParams(url) {
    var args=new Object();
    var pairs=url.split("?");
    if(pairs.length<=1)
        return args;
    pairs = pairs[1].split("&");
    for(var i=0;i<pairs.length;i++){
        var pos=pairs[i].indexOf('=');//查找name=value
        if(pos==-1) continue;//如果没有找到就跳过
        var argname=pairs[i].substring(0,pos);//提取name
        var value=pairs[i].substring(pos+1);//提取value
        args[argname]=unescape(value);//存为属性
    }
    return args;
}

function getStringLen(str) {
    str=str.replace(/[^\x00-\xff]/g, 'xx');
    return str.length;
}

function checkUsername(str){
    var res = {status:true,msg:'','type':'loginname','code':0};
    if (/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/.test(str)) {
        res.type='email';
        if (str.length<8) {
            res.status=false;
            res.code=1;
            res.msg="email 地址太短，请检查正确性";
        }
        if (str.length>40) {
            res.status=false;
            res.code=2;
            res.msg="email 地址长度不能超过40个字符";
        }
    }
    else if (/^1[1-9]\d{9}$/.test(str)) {
        res.type='mobile';
    }
    else if (/^1[1-9]\d{9}$/.test(str)) {
        res.status=false;
        res.code=3;
        res.msg="登录名不能为纯数字，手机号应为11位数字";
    }
    else if (!/^[\w\u4E00-\u9FA5-\.]+$/.test(str)) {
        res.status=false;
        res.code=4;
        res.msg="登录名只能包含数字、字母、下划线、中文、中划线和点";
    }
    else if (getStringLen(str)<5) {
        res.status=false;
        res.code=5;
        res.msg="登录名不能少于5位字符，一个中文算2位字符";
    }
    else if (getStringLen(str)>30) {
        res.status=false;
        res.code=6;
        res.msg="登录名不能多于30位字符，一个中文算2位字符";
    }
    // else if (/^[\.a-zA-Z_-]{5,30}$/.test(str)) {

    // }
    // else{
    // 	res.status=false;
    // 	res.msg="登录名只允许包含字母、数字、下划线、中划线和点";
    // }
    // alert(/^[\w\u4E00-\u9FA5-_\.]+$/.test(str))
    // alert(res.msg)
    // res.status=false;
    return res;
}
function checkPassword(password){
    if(getStringLen(password)>20 || getStringLen(password)<6){
        return getLang("password_too_simple");
    }
    else if (is_password(password)) {
        return true;
    }
    else{
        return getLang("password_too_simple");
    }
}

function getReturnUrl(){
    var returnUrl = getUrlParam('redirect_uri');
    if(!returnUrl){
        returnUrl = $.cookie("redirect_uri");
        if(returnUrl === null || returnUrl === ""){
            if (document.referrer && document.referrer.indexOf(document.domain)==0) {
                returnUrl = document.referrer;
            }
            else{
                returnUrl = "/dashboard";
            }
        }
    }
    if(returnUrl === null || returnUrl === ""){
        returnUrl = "/dashboard";
    }
    return returnUrl;
}

function jumpNow(redirect_uri)
{
    if (redirect_uri!=undefined){
        document.location.href = redirect_uri;
    }
    else {
        document.location.href = getReturnUrl();
    }
}

function getJsonLength(jsonData){
    var jsonLength = 0;
    for(var item in jsonData){
        jsonLength++;
    }
    return jsonLength;
}

var isWeiXin = function(){
    var ua = window.navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i) == 'micromessenger'){
        return true;
    }else{
        return false;
    }
}

//检查登录框中用户输出的账号类型
function checkIdentityType()
{
    // existing-area-select show-area-select identity
    var value = $("#identity").val();
    if(value.match(/[^\w\._\-@]+/)){
        value = value.replace(/[^\w\._\-@]+/, "");
        $("#identity").val(value);
    }

    if(value.match(/[^\d]+/)){
        console.info('not all number');
        if ($("#existing-area-select").hasClass("show-area-select")) {
            $("#existing-area-select").removeClass("show-area-select").addClass("hide-area-select");
        }
    }
    else if (value.length>4) {
        $("#existing-area-select").removeClass("hide-area-select").addClass("show-area-select");
    }
}

//是否正在展示微信授权登录的二维码
var onShowWeixinQRCode=false;
function checkWeixinQRLoginStatus(for_bind)
{
    $.ajax({
            type: 'post'
            , dataType: 'json'
            , url: "/sign/wechat_check_qr_login"+(for_bind==1?'/for_bind/1':'')
            , success: function (data, textStatus, jqXHR) {
                if (onShowWeixinQRCode && data.code != 4) {
                    if (data.code == 30 || data.code == 31 || data.code == 32) {
                        $("#weixinQR").find("strong").show().text(getLang("binding_failed"));
                        $(document).dialog({
                            type: "notice"
                            , position: "bottom"
                            , dialogClass: "dialog_warn"
                            , infoText: data.msg
                            , autoClose: 5000
                            , overlayShow: false
                        });
                    }
                    else {
                        $("#weixinQR").find("strong").show().text(data.msg);
                    }
                }
                else if (onShowWeixinQRCode && data.code == 4) {
                    $("#weixinQR").find("strong").hide();
                }

                if (data.code == 0) {
                    document.location.href = "/sign/bind_account";
                }
                else if (data.code == 1 || data.code == 5) {
                    jumpNow();
                }
                else if (data.code == 33 || data.code == 34) {
                    onShowWeixinQRCode = false;
                    reloadPage();
                }
                else if (data.code == 30 || data.code == 31 || data.code == 32) {
                    onShowWeixinQRCode = false;
                }
                else{
                    if (onShowWeixinQRCode) {
                        setTimeout(function () {
                            checkWeixinQRLoginStatus(for_bind);
                        }, 1000);
                    }
                }
            }
            , error: function (XMLHttpRequest, textStatus, errorThrown) {
                $("#weixinQR").find("strong").show().text(textStatus);
            }
        }
    );
}

function weixinLogin(for_bind)
{
    if (for_bind==1){
        url = '/sign/wechat_login/for_bind/1';
    }
    else{
        url = '/sign/wechat_login';
    }
    if (isWeiXin()) {
        //在微信浏览器里，则跳去微信授权登录
        document.location.href = url;
    }
    else{
        //如果有微信开放平台账号，则使用开放平台登录
        if (haveWeixinOpen){
            document.location.href = url+'?open=1';
        }
        else {
            //显示微信登录二维码
            $(document).dialog({
                titleShow: false,
                overlayClose: true,
                content: '<div id="weixinQR"><span><img src="" onload="checkWeixinQRLoginStatus('+(for_bind==1?1:0)+')">' +
                    '<strong></strong></span>' +
                    '<div class="text">' +
                    getLang("sign_in_with_wechat_scanning_code")+'</div></div>',
                buttonTextConfirm:getLang("cancel"),
                onShow: function() {
                    onShowWeixinQRCode = true;
                    random = Math.random();
                    random = 'h' + random;
                    $("#weixinQR").find("img").attr("src", "/sign/wechat_login_qr?rand="+random);
                    $("#weixinQR").find("span").bind("click", function (e) {
                        onShowWeixinQRCode = true;
                        $("#weixinQR").find("img").attr("src", "/sign/wechat_login_qr?rand="+random);
                        $("#weixinQR").find("strong").hide();
                    });
                },
                onClosed: function() {
                    onShowWeixinQRCode = false;
                }
            });

        }
    }
}

function qqLogin()
{
    //以下为按钮点击事件的逻辑。注意这里要重新打开窗口
    //否则后面跳转到QQ登录，授权页面时会直接缩小当前浏览器的窗口，而不是打开新窗口
    window.open("/sign/qq_login","TencentLogin","width=450,height=410,menubar=0,scrollbars=1, resizable=1,status=1,titlebar=0,toolbar=0,location=1");
}

function changeLanguage(lang)
{
    if (lang=="selected"){
        return false;
    }
    url = document.location.href;
    tmp = url.split("#");
    tmp2 = tmp[0].split("?");
    args = getUrlParms();
    args.lang = lang;
    params = [];
    for (var i in args){
        params.push(i+"="+args[i]);
    }
    url = tmp2[0] + "?" + params.join("&");
    if (tmp.length>1){
        url += "#"+tmp[1];
    }
    document.location.href = url;
}

/*
 * 根据语言键名和参数返回当前语言类型下的翻译文本
 * @param string key 语言键名
 * @param object param 翻译里的变量参数及值，如果没有可以不传此参数
 * @return string 返回 当前语言类型下的翻译文本
 * */
function getLang(key, param)
{
    if (typeof language == "object"){
        if (typeof language[key] == "string"){
            key = language[key];
            if (typeof param == "object"){
                $.each(param, function (index, value) {
                    // alert(typeof value.values)
                    if (typeof value == "object" && typeof value.value!="undefined"){
                        if (value.value<=1){
                            reg = new RegExp("<--singular(.*?)\\{\\$"+index+"\\}(.*?)-->", "g");
                            key= key.replace(reg, "$1"+value.value+"$2");
                            reg = new RegExp("<--plural(.*?)\\{\\$"+index+"\\}(.*?)-->", "g");
                            key= key.replace(reg, "");
                        }
                        else{
                            reg = new RegExp("<--plural(.*?)\\{\\$"+index+"\\}(.*?)-->", "g");
                            key= key.replace(reg, "$1"+value.value+"$2");
                            reg = new RegExp("<--singular(.*?)\\{\\$"+index+"\\}(.*?)-->", "g");
                            key= key.replace(reg, "");
                        }
                        reg = new RegExp("\\{\\$"+index+"\\}", "g");
                        key= key.replace(reg, value.value);
                    }
                    else {
                        reg = new RegExp("\\{\\$"+index+"\\}", "g");
                        key= key.replace(reg, value);
                    }
                });
            }
        }
    }
    return key;
}