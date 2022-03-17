<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="favicon.ico">

  <title><?php echo YiluPHP::I()->lang('please_login'); ?></title>
    <?php echo load_static('/include/css_bootstrap.shtml'); ?>
    <?php echo load_static('/include/css_dialog.shtml'); ?>
    <?php echo load_static('/include/css_base.shtml'); ?>
    <?php echo load_static('/include/css_sign.shtml'); ?>
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
<div class="website_index">
    <a href="<?php echo $config['website_index']; ?>"><?php echo YiluPHP::I()->lang('website_home'); ?></a>
</div>
<?php if(!empty($config['multi_Lang'])): ?>
<div class="language_handle">
    <a href="javascript:changeLanguage('<?php echo YiluPHP::I()->current_lang()=='cn' ?'selected':'cn'; ?>');" class="<?php echo YiluPHP::I()->current_lang()=='cn' ?'selected':''; ?>" >简体中文</a>
    <a href="javascript:changeLanguage('<?php echo YiluPHP::I()->current_lang()=='en' ?'selected':'en'; ?>');" class="<?php echo YiluPHP::I()->current_lang()=='en' ?'selected':''; ?>" >English</a>
</div>
<?php endif; ?>

<form class="form-signin" onsubmit="return submitLoginForm(this)" method="post" style="padding-bottom: 100px;">
  <div class="text-center mb-3">
    <h1 class="h3 font-weight-normal"><?php echo YiluPHP::I()->lang('please_login'); ?></h1>
  </div>

  <div class="form-label-group">
    <input type="text"  id="identity" name="identity" class="form-control" placeholder="<?php echo YiluPHP::I()->lang('login_name_placeholder'); ?>" required autofocus onchange="checkIdentityType()">
    <label for="identity"><?php echo YiluPHP::I()->lang('login_name_placeholder'); ?></label>
  </div>

  <div class="form-label-group">
    <input type="password" id="password" name="password" class="form-control" placeholder="<?php echo YiluPHP::I()->lang('login_password'); ?>" required oninput="checkIdentityType()">
    <label for="password"><?php echo YiluPHP::I()->lang('login_password'); ?></label>
  </div>

  <div class="form-label-group" id="existing-area-select">
    <div><?php echo YiluPHP::I()->lang('your_mobile_located_in'); ?></div>
    <select class="custom-select d-block w-100" name="area_code" id="area_code_1">
      <?php foreach ($area_list as $item): ?>
        <option value="<?php echo $item['code_number']; ?>" <?php if(!empty($item['recommend'])): ?>style="color: blue"<?php endif; ?> >
          (+<?php echo $item['code_number']; echo strlen($item['code_number'])<2 ? '&nbsp;&nbsp;':''; ?>）<?php echo $item['name']; ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="custom-control custom-checkbox mb-3">
    <input type="checkbox" name="remember_me" class="custom-control-input" id="remember_me">
    <label class="custom-control-label" for="remember_me"><?php echo YiluPHP::I()->lang('stay_logged_in'); ?></label>
  </div>

    <div  style="color: #aaaaaa; font-size: 12px; margin-bottom: 10px; display: none;">
        <input type="checkbox" checked id="agree_agreement" value="1">
        <?php echo YiluPHP::I()->lang('have_agree_agreement', ['website_index'=>$config['website_index']]); ?>
    </div>

  <button class="btn btn-lg btn-primary btn-block" type="submit" onfocus="this.blur();"><?php echo YiluPHP::I()->lang('login_now'); ?></button>
  <div class="row mb-3 mt-3">
    <div class="col-6">
      <a href="<?php echo url_pre_lang(); ?>/find_password"><?php echo YiluPHP::I()->lang('forgot_password'); ?></a>
    </div>
      <?php if(!empty($config['open_sign_up'])): ?>
    <div class="col-6 text-right">
      <a href="<?php echo url_pre_lang(); ?>/sign/up"><?php echo YiluPHP::I()->lang('sign_up'); ?></a>
    </div>
      <?php endif; ?>
  </div>

    <?php if(!empty($config['oauth_plat']['qq']['usable']) || !empty($config['oauth_plat']['wechat']['usable'])
        || !empty($config['oauth_plat']['wechat_open']['usable']) || !empty($config['oauth_plat']['linkedin']['usable'])
        || !empty($config['oauth_plat']['alipay']['usable'])): ?>
  <div class="gray_title">
      <?php echo YiluPHP::I()->lang('login_use_following_platform'); ?>
  </div>
  <div class="checkbox">
    <a href="javascript:weixinLogin();" id="wxlogin_btn">
          <span>
            <img src="/img/icons.png" height="47" style="margin-left: -53px;">
          </span>
    <br/><?php echo YiluPHP::I()->lang('user_identity_type_WX'); ?>
    </a>
      <a href="javascript:qqLogin();" id="qqlogin_btn">
              <span>
                <img src="/img/icons.png" height="47" style="margin-left: 4px;">
              </span>
          <br/><?php echo YiluPHP::I()->lang('user_identity_type_QQ'); ?>
      </a>
      <a href="<?php echo url_pre_lang(); ?>/sign/alipay_login" id="alipaylogin_btn">
              <span>
                <img src="/img/icons.png" height="47" style="margin-left: -112px;">
              </span>
          <br/><?php echo YiluPHP::I()->lang('user_identity_type_ALIPAY'); ?>
      </a>
  </div>
    <?php endif; ?>
</form>

<?php echo load_static('/include/js_jquery.shtml'); ?>
<?php echo load_static('/include/js_jquery_cookie.shtml'); ?>
<script src="<?php echo url_pre_lang(); ?>/config_js" type="text/javascript"></script>
<?php echo load_static('/include/js_jsencrypt.shtml'); ?>
<?php echo load_static('/include/js_dialog_diy.shtml'); ?>
<?php echo load_static('/include/js_jweixin.shtml'); ?>
<?php echo load_static('/include/js_base.shtml'); ?>
<script type="text/javascript">
    //是否可以使用微信开放平台授权登录
    var haveWeixinOpen = <?php echo empty($config['oauth_plat']['wechat_open']['usable'])?'false':'true'; ?>;
</script>
<?php echo load_static('/include/js_sign_in.shtml'); ?>
<script src="/js/language/<?php echo YiluPHP::I()->current_lang(); ?>.js"></script>
<script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? "https://" : "http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1278278388'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s4.cnzz.com/z_stat.php%3Fid%3D1278278388' type='text/javascript'%3E%3C/script%3E"));</script>
</body>
</html>
