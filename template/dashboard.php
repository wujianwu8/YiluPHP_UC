<!--{use_layout layout/main}-->

<?php
    $head_info = [
        'title' => $app->lang('home_page'),
    ];
?>

<!--<p>欢迎使用YiluPHP框架!<a href="/sign/out">退出登录</a></p>-->
<!--<strong>这是YiluPHP管理后台主页</strong>-->
<!--<p>当前版本: --><?php //echo get_version(); ?><!--</p>-->
<!--<p>官网地址: <a href="https://www.YiluPHP.com">www.YiluPHP.com</a></p>-->

<div class="container">
    <div class="border-bottom">
        <h2 class="h2"><?php echo $app->lang('home_page'); ?></h2>
    </div>
    <p class="lead text-muted mt-5"><?php echo $app->lang('welcome_back'); ?></p>
</div>