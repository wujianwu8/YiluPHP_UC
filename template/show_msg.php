<html xmlns="http://www.w3.org/1999/html">
<head>
    <meta http-equiv=Content-Type content="text/html;charset=utf-8">
    <title><?php echo $msg.'('.$err_code.')'; ?></title>
    <?php if($err_code===-1): ?>
    <script>
        alert("<?php echo htmlspecialchars($msg); ?>");
        document.location.href = "/?redirect_uri="+encodeURIComponent(document.location.href);
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
<p><?php echo $msg; ?></p>

<p>ERROR CODE(<?php echo $err_code; ?>)</p>
<script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? "https://" : "http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1278278388'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s4.cnzz.com/z_stat.php%3Fid%3D1278278388' type='text/javascript'%3E%3C/script%3E"));</script>
<script type="text/javascript" src="https://tajs.qq.com/stats?sId=66496946" charset="UTF-8"></script>
</body>
</html>