<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="/favicon.ico">
    <title>Api Documents</title>
    <link href="<?php echo $config['website_index']; ?>/css/vendor/bootstrap.min.css" rel="stylesheet">
    <style>
        body{
            padding: 0 0 6rem 0;
            margin: 0;
            font-size: 0.7rem;
        }
        nav{
            background: #343a40;
            color: #FFFFFF;
        }
        footer{
            position: fixed;
            bottom: 0;
            width: 100%;
            background: #e9e9e9;
            padding: 1rem;
            color: slategrey;
            font-size: 0.8rem;
        }
        main{
            padding: 0;
        }
        .error{
            text-align: center;
            color: #d00000;
        }
        a,
        a:visited{
            color: dodgerblue;
            text-decoration: none;
        }
        a:hover,
        a:active,
        a.active{
            color: orange;
        }
        nav a,
        nav a:visited,
        nav a:hover,
        nav a:active{
            border: none;
        }
        nav a.active{
            color: orange;
        }
        nav .form-inline{
            padding: 0.6rem 1rem;
        }
        nav .gray_bg_2{
            padding: 0.6rem 0.2rem 0.6rem 2rem;
            font-size: 1rem;
        }
        .menu_list{
            background: #242c35;
            color: #FFFFFF;
            padding: 0.5rem 0.2rem 2rem 2rem;
        }
        .gray_bg_2{
            background: #242c35;
        }
        .gray_bg_5{
            background: #e9e9e9;
        }
        .gray_bg_4{
            background: #d8d8d8;
        }
        .gray_bg_6{
            background: #f3f3f3;
        }
        .gray_bg_7{
            background: #f9f9f9;
        }
        .param_row{
            line-height: 1rem;
        }
        .param_row div{
            padding: 5px;
            border-bottom: #e9e9e9 1px dotted;
        }
        .param_row .row div{
            border-bottom: none;
        }
        .gray_text{
            color: steelblue;
        }
        .testing_form div{
            padding-top: 5px;
            padding-bottom: 5px;
        }
        .testing_form .col-lg-3{
            padding-top: 13px;
        }
        .testing_form input{
            font-size: 0.7rem;
        }
        @media (min-width: 576px){
            .title_align_2{
                padding-top: 13px!important;
                text-align: right;
            }
        }
        @media (min-width: 992px){
            .title_align{
                text-align: right;
            }
        }
        @media (max-width: 992px){
            .testing_form div,
            .param_row div{
                padding-left: 20px;
            }
        }
        .menu_group{
            cursor: pointer;
            padding-left: 2rem;
            margin-left: -2rem;
            padding-top: 5px;
            padding-bottom: 5px;
        }
        .menu_group:hover{
            background: #3b4754;
        }
        .menu_children{
            height: 0;
            overflow: hidden;
        }
        .menu_list a{
            padding-top: 5px;
            padding-bottom: 5px;
            display: inline-block;
            line-height: 14px;
        }
        .show_children_menu{
            height: auto;
            overflow: visible;
        }
    </style>
    <link href="<?php echo $config['website_index']; ?>/css/vendor/dialog.css" rel="stylesheet">
    <link href="<?php echo $config['website_index']; ?>/jsonFormater/jsonFormater.css" rel="stylesheet">
    <script src="<?php echo $config['website_index']; ?>/js/vendor/jquery-3.4.1.min.js"></script>
    <script src="<?php echo $config['website_index']; ?>/js/vendor/dialog.diy.js" type="text/javascript"></script>
    <script src="<?php echo $config['website_index']; ?>/jsonFormater/jsonFormater.js"></script>
</head>

<body>

<nav class="row">
    <div class="col-lg-2 gray_bg_2">
        Api Documents
    </div>
    <div class="col-lg-10">
        <form class="form-inline text-right" action="">
            <div class="d-inline-block mr-2">
                <select class="custom-select" name="version" id="version">
                    <option value="">Version</option>
                    <?php foreach ($version_list as $item): ?>
                        <option value="<?php echo $item;?>" <?php echo $item==$version?'selected':''; ?> >
                            <?php echo $item;?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="text" class="form-control mr-2" id="keyword" name="keyword" placeholder="keyword"
            value="<?php echo isset($_GET['keyword'])?$_GET['keyword']:''; ?>" onmouseover="this.select()">
            <button class="btn btn-primary" type="submit">Search</button>
            <?php if ($is_login): ?>
                <a href="?logout=true" class="btn btn-outline-info ml-3">Logout</a>
            <?php endif; ?>
        </form>
    </div>
</nav>

<main class="row">
    <div class="col-lg-2 menu_list">
        <h6>Menu List</h6>
        <?php $title_index = 0; ?>
        <?php foreach ($menu_list as $key => $item): ?>
            <?php if (strlen($key)>=32): ?>
                <?php $title_index++; ?>
                <div class="h6 gray_text menu_group" title_index="<?php echo $title_index;?>"><?php echo $item[0]['group'];?></div>
                <div class="menu_children show_children_menu">
            <?php endif; ?>
            <?php foreach ($item as $api): ?>
                <div <?php echo strlen($key)<32?'':'class="pl-3"' ?> >
                    <?php
                        $params = ['version'=>$version, 'path'=>$api['path']];
                        if (isset($_GET['keyword']) && trim($_GET['keyword'])!=''){
                            $params['keyword'] = trim($_GET['keyword']);
                        }
                    ?>
                    <a href="<?php echo add_url_params(get_url(), $params);?>"
                        <?php echo !empty($api_info)&&$api['path']==$api_info['path']?'class="active"':''; ?> >
                        <?php echo $api['name'];?>
                    </a>
                </div>
            <?php endforeach; ?>
            <?php if (strlen($key)>=32): ?>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if (empty($menu_list)): ?>
            <div class="pt-4">No api to show. </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-5">
        <?php if (empty($api_info)): ?>
            <div class="pt-5">No api info to show. </div>
        <?php else: ?>
            <div class="row param_row">
                <div class="col-lg-2 gray_bg_4 title_align pt-3">Name: </div>
                <div class="col-lg-10 pt-3"><?php echo $api_info['name']; ?></div>
                <div class="col-lg-2 gray_bg_4 title_align">Desc: </div>
                <div class="col-lg-10"><?php echo $api_info['desc']; ?></div>
                <div class="col-lg-2 gray_bg_4 title_align">URI: </div>
                <div class="col-lg-10"><?php echo $api_info['uri']; ?></div>
                <div class="col-lg-2 gray_bg_4 title_align">Method: </div>
                <div class="col-lg-10"><?php echo $api_info['method']; ?></div>
                <div class="col-lg-2 gray_bg_4 title_align">Params: </div>
                <div class="col-lg-10"></div>
                <div class="col-lg-12 row gray_bg_5 ml-0 pl-0">
                    <div class="col-sm-2">Key</div>
                    <div class="col-sm-1">Type</div>
                    <div class="col-sm-2">Name</div>
                    <div class="col-sm-1">Required</div>
                    <div class="col-sm-6 pl-3">Remark</div>
                </div>
                <?php foreach ($api_info['params'] as $key => $param): ?>
                <div class="col-lg-12 row <?php echo $key%2==0?'':'gray_bg_6'; ?> ml-0 pl-0">
                    <div class="col-sm-2"><?php echo $param['param_key']; ?></div>
                    <div class="col-sm-1"><?php echo $param['data_type']; ?></div>
                    <div class="col-sm-2"><?php echo $param['param_name']; ?></div>
                    <div class="col-sm-1"><?php echo $param['required']; ?></div>
                    <div class="col-sm-6 pl-3"><?php echo $param['remark']; ?></div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($api_info['params'])): ?>
                    <div class="col-lg-12 pt-5"></div>
                <?php endif; ?>
                <div class="col-lg-2 gray_bg_4 title_align pt-3">Return: </div>
                <div class="col-lg-10 pt-3">
                    <pre><?php echo $api_info['return']; ?></pre>
                </div>
                <div class="col-lg-2 gray_bg_4 title_align">Exception: </div>
                <div class="col-lg-10">
                    <pre><?php echo $api_info['exception']; ?></pre>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-5">
        <form action="<?php echo $api_info['uri']; ?>" class="row needs-validation testing_form"
              method="<?php echo strpos(strtolower($api_info['method']), 'post')==false ? 'post':'get'; ?>">
            <div class="col-sm-3 gray_bg_4 title_align_2"></div>
            <div class="col-sm-9"><h5>Testing</h5></div>
            <?php if (empty($api_info)): ?>
                <div class="col-sm-3 gray_bg_4"></div>
                <div class="col-sm-9" id="testResult">No api info to test. </div>
            <?php else: ?>
                <div class="col-sm-3 gray_bg_4 title_align_2">dtype</div>
                <div class="col-sm-9">
                    <input type="hidden" name="dtype" value="json">
                    <input type="text" class="form-control" value="json" disabled>
                </div>
                <?php foreach ($api_info['params'] as $param): ?>
                <div class="col-sm-3 gray_bg_4 title_align_2"><?php echo $param['param_name']; ?></div>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="<?php echo $param['param_key']; ?>"
                           placeholder="<?php echo $param['data_type'].'  '.$param['required']; ?>">
                </div>
                <?php endforeach; ?>
                <div class="col-sm-3 gray_bg_4"> </div>
                <div class="col-sm-9">
                    <button class="btn btn-primary" type="submit">Submit</button>
                </div>
                <div class="col-sm-3 gray_bg_4 title_align_2">Result</div>
                <div class="col-sm-9" id="testResult"></div>
            <?php endif; ?>
        </form>
    </div>
</main>

<footer>
    <div class="container">
        <div>
            Technical support:
            <a target="_blank" href="<?php echo $config['website_index']; ?>">www.yiluphp.com</a>
        </div>
    </div>
</footer>

<script>
    //设置cookie
    function setCookie(name,value){
        if(!name||!value) return;
        var Days = 30;//默认30天
        var exp  = new Date();
        exp.setTime(exp.getTime() + Days*24*60*60*1000);
        document.cookie = name + "="+ encodeURIComponent(value) +";expires="+ exp.toUTCString();
    }

    //获取cookie
    function getCookie(name){
        var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
        if(arr != null) return decodeURIComponent(arr[2]);
        return null;
    }

    //删除cookie
    function delCookie(name){
        var exp = new Date();
        exp.setTime(exp.getTime() - 1);
        var cval=getCookie(name);
        if(!cval) document.cookie=name +"="+cval+";expires="+exp.toUTCString();
    }

    $(document).ready(function(){
        var jsFormatterDemo = new JsonFormatter({dom:$("#testResult")});
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                event.stopPropagation();
                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return false;
                }

                var params = {
                };
                var inputs = $(form).serializeArray();
                for (var index in inputs) {
                    var item = inputs[index];
                    params[item.name] = item.value;
                }
                var toast = $(document).dialog({
                    type : 'toast',
                    content: '<span class="info-text">正在请求...</span>',
                    autoClose: 2500
                });
                $.ajax({
                        type: form.method
                        , dataType: 'json'
                        , url: form.action
                        , data: params
                        , success: function (data, textStatus, jqXHR) {
                            toast.close();
                            jsFormatterDemo.doFormat(data);
                            if (data.code == 0) {
                                $(document).dialog({
                                    overlayClose: true
                                    , titleShow: false
                                    , content: data.msg
                                    , autoClose: 3000
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
            }, false);
        });


        $(document.body).on("click", function (e) {
            //console.log($(e.target).parents(".ajax_main_content"));
            var linkDom = null;
            var action = null;
            var monitorClass = ["menu_group"];
            $.each(monitorClass, function (index, item) {
                if ($(e.target).hasClass(item)) {
                    linkDom = $(e.target);
                    action = item;
                } else if ($(e.target).parents("." + item).length > 0) {
                    linkDom = $(e.target).parents("." + item);
                    action = item;
                }
                if (linkDom !== null) {
                    return;
                }
            });

            if (linkDom != null) {
                e.preventDefault();
                if (action == "menu_group") {
                    menuCookieValue = getCookie("docs_menu_title");
                    if (menuCookieValue === null){
                        menuCookieValue = {};
                    }
                    else{
                        menuCookieValue = JSON.parse(menuCookieValue);
                    }
                    if (linkDom.next().hasClass("show_children_menu")){
                        linkDom.next().slideUp("fast",function(){
                            linkDom.next().removeClass("show_children_menu")
                        });
                        eval("menuCookieValue.tt"+linkDom.attr("title_index")+"=0");
                    }
                    else{
                        linkDom.next().addClass("show_children_menu")
                        linkDom.next().slideDown("fast");
                        eval("menuCookieValue.tt"+linkDom.attr("title_index")+"=1");
                    }
                    setCookie("docs_menu_title", JSON.stringify(menuCookieValue));
                }
            }
        });
    });

    function initMenus() {
        menuCookieValue = getCookie("docs_menu_title");
        if (menuCookieValue !== null){
            menuCookieValue = JSON.parse(menuCookieValue);
            $.each(menuCookieValue, function (index, item) {
                index = index.substring(2);
                if (item==1){
                    $("div[title_index="+index+"]").next().addClass("show_children_menu");
                }
                else {
                    $("div[title_index="+index+"]").next().removeClass("show_children_menu");
                }
            });
        }
    }
    initMenus();
</script>

</body>
</html>