<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?php echo $app->lang('forgot_password'); ?></title>
    <!--#include virtual="/include/css_bootstrap.shtml"-->
    <!--#include virtual="/include/css_dialog.shtml"-->
    <!--#include virtual="/include/css_base.shtml"-->
    <!--#include virtual="/include/css_sign.shtml"-->

    <!--#include virtual="/include/js_jquery.shtml"-->
    <!--#include virtual="/include/js_jquery_cookie.shtml"-->
    <!--#include virtual="/include/js_config_js.shtml"-->
    <!--#include virtual="/include/js_jsencrypt.shtml"-->
    <!--#include virtual="/include/js_dialog_diy.shtml"-->
    <!--#include virtual="/include/js_base.shtml"-->
    <style>
        #select_method a{
            width: 100%;
            margin: 10px 0;
        }
        #by_email,
        #by_mobile{
            display: none;
        }

    </style>
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
    <a href="javascript:changeLanguage('<?php echo $app->current_lang()=='cn' ?'selected':'cn'; ?>');" class="<?php echo $app->current_lang()=='cn' ?'selected':''; ?>" >简体中文</a>
    <a href="javascript:changeLanguage('<?php echo $app->current_lang()=='en' ?'selected':'en'; ?>');" class="<?php echo $app->current_lang()=='en' ?'selected':''; ?>" >English</a>
</div>

<div class="form-signin">
    <div id="select_method">
        <a class="btn btn-primary" href="#by_mobile"><?php echo $app->lang('reset_password_by_mobile'); ?> »</a>
        <a class="btn btn-primary" href="#by_email"><?php echo $app->lang('reset_password_by_email'); ?> »</a>
        <a href="/" class="btn btn-xs btn-default"><?php echo $app->lang('return_to_login'); ?></a>
    </div>

    <div id="by_mobile">
        <form name="by_mobile" method="post" onsubmit="return checkMobileCodeForm(this)">
            <div><?php echo $app->lang('your_mobile_located_in'); ?></div>
            <div class="mb-3">
                <select class="custom-select d-block w-100" name="area_code" id="area_code_2">
                    <?php foreach ($area_list as $item): ?>
                        <option value="<?php echo $item['code_number']; ?>" <?php if(!empty($item['recommend'])): ?>style="color: blue"<?php endif; ?> >
                            (+<?php echo $item['code_number']; echo strlen($item['code_number'])<2 ? '&nbsp;&nbsp;':''; ?>）<?php echo $item['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <input type="number" name="mobile" id="mobile" class="form-control hidden_input" placeholder="<?php echo $app->lang('mobile_number'); ?>" value="" required autofocus>
            </div>


            <div class="mb-3">
                <div class="input-group">
                    <input type="number" class="form-control" name="verify_code" value="" placeholder="Verify Code" maxlength="6" minlength="4" oninput="if(value.length>6) value=value.slice(0,6)" required>
                    <div class="input-group-append">
                        <button type="button" id="btn_send_sms_code" class="btn btn-secondary" use_for="find_password" style="width: 130px;"><?php echo $app->lang('send_code'); ?></button>
                    </div>
                </div>
            </div>
            <button class="btn btn-lg btn-primary btn-block" type="submit" id="checkMobileCodeBtn"><?php echo $app->lang('verify_account'); ?></button>
        </form>
        <div class="mt-4">
            <a href="#by_email" style="margin-right: 20px;"><?php echo $app->lang('reset_password_by_email'); ?></a>
            <a href="/"><?php echo $app->lang('return_to_login'); ?></a>
        </div>
    </div>

    <div id="by_email">
        <form name="new-account-form" method="post" onsubmit="return checkEmailCodeForm(this)">
            <div class="mb-3">
                <input type="email" name="email" id="email" class="form-control hidden_input" placeholder="<?php echo $app->lang('Email'); ?>" value="" required autofocus>
            </div>

            <div class="mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="verify_code" value="" placeholder="Verify Code" maxlength="6" minlength="4" oninput="if(value.length>6) value=value.slice(0,6)" required>
                    <div class="input-group-append">
                        <button type="button" id="btn_send_email_code" class="btn btn-secondary" style="width: 130px;"><?php echo $app->lang('send_code'); ?></button>
                    </div>
                </div>
            </div>
            <button class="btn btn-lg btn-primary btn-block" type="submit" id="checkEmailCodeBtn"><?php echo $app->lang('verify_account'); ?></button>
        </form>
        <div class="mt-4">
            <a href="#by_mobile" style="margin-right: 20px;"><?php echo $app->lang('reset_password_by_mobile'); ?></a>
            <a href="/"><?php echo $app->lang('return_to_login'); ?></a>
        </div>
    </div>

    <div id="reset_by_mobile">
        <form class="mb-3" name="new-account-form" method="post" onsubmit="return resetPasswordForm(this)">
            <div class="mb-3"><?php echo $app->lang('reset_password_by_mobile'); ?></div>
            <div class="mb-3"><strong class="account"></strong></div>
            <input type="hidden" class="hidden_input" name="mobile">
            <input type="hidden" name="verify_code">
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="<?php echo $app->lang('new_password'); ?>" value="" required>
            </div>

            <div class="mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="<?php echo $app->lang('confirm_new_password'); ?>" value="" required>
            </div>

            <button class="btn btn-lg btn-primary btn-block reset_password_btn" type="submit"><?php echo $app->lang('save'); ?></button>
        </form>
        <a href="#by_mobile" class="btn btn-xs btn-default pl-0 resend_link"><?php echo $app->lang('resend_verify_code'); ?></a>
        <a href="#by_email" class="btn btn-xs btn-default pl-0"><?php echo $app->lang('reset_password_by_email'); ?></a>
        <a href="/" class="btn btn-xs btn-default pl-0"><?php echo $app->lang('return_to_login'); ?></a>
    </div>

    <div id="reset_by_email">
        <form class="mb-3" name="new-account-form" method="post" onsubmit="return resetPasswordForm(this)">
            <div class="mb-3"><?php echo $app->lang('resetting_password_by_email'); ?></div>
            <div class="mb-3"><strong class="account"></strong></div>
            <input type="hidden" class="hidden_input" name="email">
            <input type="hidden" name="verify_code">
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="<?php echo $app->lang('new_password'); ?>" value="" required>
            </div>

            <div class="mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="<?php echo $app->lang('confirm_new_password'); ?>" value="" required>
            </div>

            <button class="btn btn-lg btn-primary btn-block reset_password_btn" type="submit"><?php echo $app->lang('save'); ?></button>
        </form>
        <a href="#by_email" class="btn btn-xs btn-default pl-0 resend_link"><?php echo $app->lang('resend_verify_code'); ?></a>
        <a href="#by_mobile" class="btn btn-xs btn-default pl-0"><?php echo $app->lang('reset_password_by_mobile'); ?></a>
        <a href="/" class="btn btn-xs btn-default pl-0"><?php echo $app->lang('return_to_login'); ?></a>
    </div>
</div>


<!--#include virtual="/include/js_find_password.shtml"-->
<!--#include virtual="/include/js_no_logged_in.shtml"-->
<script src="/js/language/<?php echo $app->current_lang(); ?>.js"></script>
<script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? "https://" : "http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1278278388'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s4.cnzz.com/z_stat.php%3Fid%3D1278278388' type='text/javascript'%3E%3C/script%3E"));</script>
<script type="text/javascript" src="https://tajs.qq.com/stats?sId=66496946" charset="UTF-8"></script>
</body>
</html>