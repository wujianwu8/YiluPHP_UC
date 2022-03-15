<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>


        </div><!--/.col-xs-12.col-sm-9-->

        <div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar">
          <div class="btn-toggle-nav visible-xs" data-toggle="offcanvas">右侧栏</div>
          <div class="list-group">
            <a href="<?php echo url_pre_lang(); ?>/user/mdf_infor" class="list-group-item <?php echo $_SERVER['PATH_INFO']=='/user/mdf_infor'?'active':''; ?> ">个人信息</a>
            <a href="<?php echo url_pre_lang(); ?>/user/mdf_avatar" class="list-group-item <?php echo $_SERVER['PATH_INFO']=='/user/mdf_avatar'?'active':''; ?> ">修改头像</a>
          <?php if(empty($self_info['loginname']) && empty($self_info['email']) && empty($self_info['mobile'])): ?>
            <!-- 第三方登录用户，如果没有登录账号则显示此菜单 -->
            <a href="<?php echo url_pre_lang(); ?>/user/bind_account" class="list-group-item <?php echo $_SERVER['PATH_INFO']=='/user/bind_account'?'active':''; ?> ">绑定已有账号</a>
            <a href="<?php echo url_pre_lang(); ?>/user/reg_account" class="list-group-item <?php echo $_SERVER['PATH_INFO']=='/user/reg_account'?'active':''; ?> ">注册登录账号</a>
          <?php else: ?>
            <a href="<?php echo url_pre_lang(); ?>/user/mdf_pd" class="list-group-item <?php echo $_SERVER['PATH_INFO']=='/user/mdf_pd'?'active':''; ?> ">修改密码</a>
            <a href="<?php echo url_pre_lang(); ?>/user/set_account" class="list-group-item <?php echo $_SERVER['PATH_INFO']=='/user/set_account'?'active':''; ?> ">设置登录账号</a>
          <?php endif; ?>
			<?php if(check_permission('manage_user:view')): ?>
			<a href="#" class="list-group-item">管理用户</a>
			<?php endif; ?>
			<?php if(check_permission('manage_system:view')): ?>
			<a href="#" class="list-group-item">管理系统</a>
			<?php endif; ?>
			<?php if(check_permission('manage_role:view')): ?>
			<a href="#" class="list-group-item">角色管理</a>
			<?php endif; ?>
          </div>
        </div><!--/.sidebar-offcanvas-->
      </div><!--/row-->

      <hr class="hidden-xs">

      <footer class="hidden-xs">
        <p>
            &copy; 2019-2030 YiluPHP.com 版权所有
            <a href="https://beian.miit.gov.cn/" target="_blank">粤ICP备19143214号</a>
        </p>
      </footer>

    </div><!--/.container-->

  </body>
</html>
