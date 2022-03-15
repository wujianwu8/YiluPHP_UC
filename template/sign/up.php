<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="/favicon.ico">
    <title><?php echo YiluPHP::I()->lang('sign_up_account'); ?></title>
      <?php echo load_static('/include/css_bootstrap.shtml'); ?>
      <?php echo load_static('/include/css_dialog.shtml'); ?>
      <?php echo load_static('/include/css_base.shtml'); ?>
      <?php echo load_static('/include/css_sign.shtml'); ?>

      <?php echo load_static('/include/js_jquery.shtml'); ?>
      <?php echo load_static('/include/js_jquery_cookie.shtml'); ?>
      <script src="<?php echo url_pre_lang(); ?>/config_js" type="text/javascript"></script>
      <?php echo load_static('/include/js_jsencrypt.shtml'); ?>
      <?php echo load_static('/include/js_dialog_diy.shtml'); ?>
      <?php echo load_static('/include/js_base.shtml'); ?>
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
      <div class="language_handle">
          <a href="javascript:changeLanguage('<?php echo YiluPHP::I()->current_lang()=='cn' ?'selected':'cn'; ?>');" class="<?php echo YiluPHP::I()->current_lang()=='cn' ?'selected':''; ?>" >简体中文</a>
          <a href="javascript:changeLanguage('<?php echo YiluPHP::I()->current_lang()=='en' ?'selected':'en'; ?>');" class="<?php echo YiluPHP::I()->current_lang()=='en' ?'selected':''; ?>" >English</a>
      </div>

      <form class="form-signin" onsubmit="return submitRegisterForm(this)" method="post">
        <h2 class="form-signin-heading"><?php echo YiluPHP::I()->lang('sign_up_account'); ?></h2>
          <div><?php echo YiluPHP::I()->lang('your_mobile_located_in'); ?></div>
          <div class="mb-3">
          <select class="custom-select d-block w-100" id="area_code_2" name="area_code">
              <?php foreach ($area_list as $item): ?>
                <option value="<?php echo $item['code_number']; ?>" <?php if(!empty($item['recommend'])): ?>style="color: blue"<?php endif; ?> >
                    (+<?php echo $item['code_number']; echo strlen($item['code_number'])<2 ? '&nbsp;&nbsp;':''; ?>）<?php echo $item['name']; ?>
                </option>
              <?php endforeach; ?>
          </select>
          </div>

        <div class="mb-3">
              <input type="number" id="mobile" name="mobile" class="form-control" placeholder="<?php echo YiluPHP::I()->lang('mobile_number'); ?>" required autofocus>
        </div>

        <div class="mb-3">
            <label for="inputPassword" class="sr-only">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="<?php echo YiluPHP::I()->lang('login_password'); ?>" required>
        </div>

        <div class="mb-3">
          <label for="inputPassword" class="sr-only">Confim Password</label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="<?php echo YiluPHP::I()->lang('confirm_login_password'); ?>" required>
        </div>

          <div class="mb-3">
              <div class="input-group">
                  <input type="number" class="form-control" name="verify_code" id="verify_code" placeholder="<?php echo YiluPHP::I()->lang('verify_code'); ?>" maxlength="6" minlength="4" oninput="if(value.length>6) value=value.slice(0,6)" required>
                  <div class="input-group-append">
                      <button type="button" id="btn_send_sms_code" class="btn btn-secondary" use_for="sign_up"><?php echo YiluPHP::I()->lang('send_code'); ?></button>
                  </div>
              </div>
          </div>
          <div style="color: #aaaaaa; font-size: 12px; margin-bottom: 10px;">
              <input type="checkbox" checked id="agree_agreement" value="1">
              <?php echo YiluPHP::I()->lang('have_agree_agreement', ['website_index'=>$config['website_index']]); ?>
          </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit" id="registerButton"><?php echo YiluPHP::I()->lang('sign_up'); ?></button>
        <div class="checkbox" style="overflow: hidden;zoom:1;margin-top: 20px;margin-bottom: 130px;">
          <a href="<?php echo url_pre_lang(); ?>/find_password" style="float: left;"><?php echo YiluPHP::I()->lang('forgot_password'); ?></a>
            <?php if(!is_webview()): ?>
          <a href="<?php echo url_pre_lang(); ?>/sign/in" style="float: right;"><?php echo YiluPHP::I()->lang('sign_in'); ?></a>
            <?php endif; ?>
        </div>
      </form>

      <?php echo load_static('/include/js_no_logged_in.shtml'); ?>
      <script src="/js/language/<?php echo YiluPHP::I()->current_lang(); ?>.js"></script>
      <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? "https://" : "http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1278278388'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s4.cnzz.com/z_stat.php%3Fid%3D1278278388' type='text/javascript'%3E%3C/script%3E"));</script>
  </body>
</html>