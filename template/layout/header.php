<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <title>用户中心</title>

    <!-- Bootstrap core CSS -->
    <link href="/static/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="/static/bootstrap/css/ie10-viewport-bug-workaround.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/static/bootstrap/css/offcanvas.css" rel="stylesheet">
    <link href="/static/css/vision_2.css" rel="stylesheet">
    <link href="/static/bootstrap/css/bootstrapValidator.min.css" rel="stylesheet">
    <!-- flavr 弹出层所需 -->
    <link href="/static/bootstrap/css/animate.css" rel="stylesheet">
    <link href="/static/bootstrap/css/flavr.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="/static/bootstrap/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="/static/bootstrap/js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/static/js/jquery-2.1.4.min.js"></script>
    <script>window.jQuery || document.write('<script src="/static/bootstrap/js/vendor/jquery.min.js"><\/script>')</script>
    <script src="/static/bootstrap/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="/static/bootstrap/js/ie10-viewport-bug-workaround.js"></script>
    <script src="/static/bootstrap/js/offcanvas.js"></script>
    <script src="/static/bootstrap/js/bootstrapValidator.min.js"></script>
    <!-- flavr 弹出层所需 -->
    <script src="/static/bootstrap/js/common.js"></script>
    <script src="/static/bootstrap/js/flavr.min.js"></script>
  </head>

  <body>
    <nav class="navbar navbar-fixed-top navbar-inverse">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php echo url_pre_lang(); ?>/home">用户中心</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">切换系统 <span class="caret"></span></a>
              <ul class="dropdown-menu">
              	<?php foreach ($system_list as $sys): ?>
				<li><a href="<?php echo $sys['index_url']; ?>"><?php echo $sys['sys_name']; ?></a></li>
				<?php endforeach; ?>
              </ul>
            </li>
            <li><a href="<?php echo url_pre_lang(); ?>/sign/out">欢迎<?php echo $self_info['nickname']; ?>回来，退出</a></li>
          </ul>
        </div><!-- /.nav-collapse -->
      </div><!-- /.container -->
    </nav><!-- /.navbar -->

    <div class="container">

      <div class="row row-offcanvas row-offcanvas-right">

        <div class="col-xs-12 col-sm-9">
        
