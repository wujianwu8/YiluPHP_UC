<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => strpos($_SERVER['REQUEST_URI'],'forbidden')===false ? $app->lang('user_list') : $app->lang('menu_blocked_user'),
];
?>

<style>
    #all_user_list .blocked{
        color: #aaaaaa;
    }
    #all_user_list .reset_password:hover,
    #all_user_list .block_user:hover{
        color: orangered;
    }
</style>

<form class="needs-validation" novalidate>
    <div class="row mb-2">
        <div class="col-md-2">
            <select class="custom-select d-block w-100" name="gender">
                <option value=""><?php echo $app->lang('gender'); ?></option>
                <option value="male"<?php echo isset($_REQUEST['gender'])&&$_REQUEST['gender']=='male'? ' selected':''; ?>><?php echo $app->lang('gender_male'); ?></option>
                <option value="female"<?php echo isset($_REQUEST['gender'])&&$_REQUEST['gender']=='female'? ' selected':''; ?>><?php echo $app->lang('gender_female'); ?></option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="nickname" placeholder="<?php echo $app->lang('nickname'); ?>" value="<?php echo isset($_REQUEST['nickname'])? $_REQUEST['nickname']:''; ?>">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="identity" placeholder="<?php echo $app->lang('login_account'); ?>" value="<?php echo isset($_REQUEST['identity'])? $_REQUEST['identity']:''; ?>">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="position" placeholder="<?php echo $app->lang('geo_position'); ?>" value="<?php echo isset($_REQUEST['position'])? $_REQUEST['position']:''; ?>">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="uid" placeholder="<?php echo $app->lang('user_id'); ?>" value="<?php echo isset($_REQUEST['uid'])? $_REQUEST['uid']:''; ?>">
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-md-2">
            <input type="date" class="form-control show_title" name="birthday_1" title="<?php echo $app->lang('begin_birthday'); ?>" value="<?php echo isset($_REQUEST['birthday_1'])? $_REQUEST['birthday_1']:''; ?>">
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control show_title" name="birthday_2" title="<?php echo $app->lang('end_birthday'); ?>" value="<?php echo isset($_REQUEST['birthday_2'])? $_REQUEST['birthday_2']:''; ?>">
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control show_title" name="reg_time_1" title="<?php echo $app->lang('begin_sign_up_time'); ?>" value="<?php echo isset($_REQUEST['reg_time_1'])? $_REQUEST['reg_time_1']:''; ?>">
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control show_title" name="reg_time_2" title="<?php echo $app->lang('end_sign_up_time'); ?>" value="<?php echo isset($_REQUEST['reg_time_2'])? $_REQUEST['reg_time_2']:''; ?>">
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control show_title" name="last_active_1" title="<?php echo $app->lang('begin_last_active_time'); ?>" value="<?php echo isset($_REQUEST['last_active_1'])? $_REQUEST['last_active_1']:''; ?>">
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control show_title" name="last_active_2" title="<?php echo $app->lang('end_last_active_time'); ?>" value="<?php echo isset($_REQUEST['last_active_2'])? $_REQUEST['last_active_2']:''; ?>">
        </div>
    </div>
    <div class="row mb-3">
        <button class="btn btn-primary btn-sm ml-3 pl-5 pr-5" type="submit"><?php echo $app->lang('search'); ?></button>
        <button class="btn btn-primary btn-sm ml-4" type="button" id="clear_form"><?php echo $app->lang('clean_up'); ?></button>
        <a class="btn btn-sm btn-outline-primary ml-4" href="#">
            <i class="fa fa-user-plus" aria-hidden="true"></i>
            <?php echo $app->lang('add_user'); ?>
        </a>
        <select class="ml-4" name="page_size">
            <option value="10">10<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="15"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='15'? ' selected':''; ?>>15<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="20"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='20'? ' selected':''; ?>>20<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="30"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='30'? ' selected':''; ?>>30<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="40"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='40'? ' selected':''; ?>>40<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="50"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='50'? ' selected':''; ?>>50<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="100"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='100'? ' selected':''; ?>>100<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="200"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='200'? ' selected':''; ?>>200<?php echo $app->lang('data_number_per_page'); ?></option>
            <option value="500"<?php echo isset($_REQUEST['page_size'])&&$_REQUEST['page_size']=='500'? ' selected':''; ?>>500<?php echo $app->lang('data_number_per_page'); ?></option>
        </select>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-sm" id="all_user_list">
        <thead>
        <tr>
            <th>ID</th>
            <th><?php echo $app->lang('avatar'); ?></th>
            <th><?php echo $app->lang('nickname'); ?></th>
            <?php if(isset($_REQUEST['identity'])): ?>
            <th><?php echo $app->lang('login_account'); ?></th>
            <?php endif; ?>
            <th><?php echo $app->lang('gender'); ?></th>
            <th><?php echo $app->lang('birthday'); ?></th>
            <th><?php echo $app->lang('geo_position'); ?></th>
            <th><?php echo $app->lang('status'); ?></th>
            <th><?php echo $app->lang('sign_up_time'); ?></th>
            <th><?php echo $app->lang('last_active_time'); ?></th>
            <th><?php echo $app->lang('operation'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($user_list as $user): ?>
        <tr _uid="<?php echo $user['uid']; ?>" <?php echo !$user['status']?' class="blocked"':''; ?>>
            <td><?php echo $user['uid']; ?></td>
            <td><img src="<?php echo $user['avatar']; ?>" width="18" height="18"></td>
            <td class="nickname"><?php echo $user['nickname']; ?></td>
            <?php if(isset($_REQUEST['identity'])): ?>
                <td><?php echo $user['identity']; ?></td>
            <?php endif; ?>
            <td><?php echo $app->lang('gender_'.$user['gender']); ?></td>
            <td><?php echo $user['birthday']; ?></td>
            <td><?php echo $app->lang($user['country']).' '.$user['province'].' '.$user['city']; ?></td>
            <td><?php echo $app->lang('user_status_'.$user['status']); ?></td>
            <td><?php echo date('Y-m-d H:i:s', $user['ctime']); ?></td>
            <td><?php echo date('Y-m-d H:i:s', $user['last_active']); ?></td>
            <td>
                <a class="detail" href="/user/detail/<?php echo $user['uid']; ?>">
                    <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                </a>
                <a class="ml-2 ajax_main_content" href="/user/grant_role/<?php echo $user['uid']; ?>">
                    <i class="fa fa-user-o" aria-hidden="true"></i>
                </a>
                <a class="ml-2 ajax_main_content" href="/user/grant_permission/<?php echo $user['uid']; ?>">
                    <i class="fa fa-cubes" aria-hidden="true"></i>
                </a>
                <a class="ml-2 reset_password" href="/user/reset_user_password"><i class="fa fa-key" aria-hidden="true"></i></a>
            <?php if(empty($user['status'])): ?>
                <a class="ml-2 unblock_user" href="/user/change_user_status"><i class="fa fa-check-square" aria-hidden="true"></i></a>
            <?php else: ?>
                <a class="ml-2 block_user" href="/user/change_user_status"><i class="fa fa-window-close" aria-hidden="true"></i></a>
            <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if(empty($user_list)): ?>
            <tr>
                <td colspan="10" class="pt-5 pb-5"><center><?php echo $app->lang('no_data'); ?></center></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php echo $app->pager->display_pages([
    'data_count' => $data_count,
    'page' => $page,
    'page_size' => $page_size,
    'a_class' => 'ajax_main_content',
    'first_page_text' => $app->lang('first_page'),
    'pre_page_text' => $app->lang('previous_page'),
    'next_page_text' => $app->lang('next_page'),
    'last_page_text' => $app->lang('last_page'),
]); ?>

<!--#include virtual="/include/js_user_list.shtml"-->
