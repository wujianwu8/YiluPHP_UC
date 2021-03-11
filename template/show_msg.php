<html xmlns="http://www.w3.org/1999/html">
<head>
    <meta http-equiv=Content-Type content="text/html;charset=utf-8">
    <title><?php echo $msg.'('.$err_code.')'; ?></title>
    <?php if($err_code===-1): ?>
        <script>
            console.log("<?php echo htmlspecialchars($msg); ?>");
            document.location.href = "<?php echo !empty($config['user_center']['host'])?$config['user_center']['host']:''; ?>/?redirect_uri="+encodeURIComponent(document.location.href);
        </script>
    <?php endif; ?>
    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?802be9112dbcdf29bc10f0eabed49dca";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>
</head>
<body>

<div class="error">
    <p><?php echo $msg; ?></p>
    <p>ERROR CODE [ <?php echo $err_code; ?> ]</p>
    <p>
        <a href="javascript:history.back(-1);">
            <?php echo YiluPHP::I()->lang('back'); ?>
        </a>
    </p>
</div>

<?php if (!empty($backtrace)): ?>
    <div style="background: peachpuff; color: #d00000;">
        <h3 style="background: brown; color: gold; padding: 0.3rem 1rem;"><?php echo YiluPHP::I()->lang('debug_mode_title'); ?></h3>
        <pre style=" padding: 0.5rem 1.5rem 0.5rem 1.5rem;">
<?php print_r($backtrace); ?>
    </pre>
    </div>
<?php endif; ?>

</body>
</html>