<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' => $app->lang('application_manage'),
];
?>

<form class="needs-validation" novalidate>
    <div class="row mb-2">
        <div class="col-md-2">
            <select class="custom-select d-block w-100" name="status">
                <option value=""><?php echo $app->lang('status'); ?></option>
                <option value="0"<?php echo isset($_REQUEST['status'])&&$_REQUEST['status']=='0'? ' selected':''; ?>>
                    <?php echo $app->lang('application_status_0'); ?>
                </option>
                <option value="1"<?php echo isset($_REQUEST['status'])&&$_REQUEST['status']=='1'? ' selected':''; ?>>
                    <?php echo $app->lang('application_status_1'); ?>
                </option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="app_id" placeholder="<?php echo $app->lang('application_id'); ?>" value="<?php echo isset($_REQUEST['app_id'])? $_REQUEST['app_id']:''; ?>">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="app_name" placeholder="<?php echo $app->lang('application_name'); ?>" value="<?php echo isset($_REQUEST['app_name'])? $_REQUEST['app_name']:''; ?>">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="user" placeholder="<?php echo $app->lang('creator_id_or_nickname'); ?>" value="<?php echo isset($_REQUEST['user'])? $_REQUEST['user']:''; ?>">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="index_url" placeholder="<?php echo $app->lang('application_web_site'); ?>" value="<?php echo isset($_REQUEST['index_url'])? $_REQUEST['index_url']:''; ?>">
        </div>
    </div>
    <div class="row mb-3">
        <button class="btn btn-primary btn-sm ml-3 pl-5 pr-5" type="submit"><?php echo $app->lang('search'); ?></button>
        <button class="btn btn-primary btn-sm ml-4" type="button" id="clear_form"><?php echo $app->lang('clean_up'); ?></button>
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
        <a href="/application/add" class="btn btn-sm btn-outline-primary ml-4 ajax_main_content">
            <i class="fa fa-plus" aria-hidden="true"></i>
            <?php echo $app->lang('add_application'); ?>
        </a>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-sm table_list" id="all_application_list">
        <thead>
        <tr>
            <th><?php echo $app->lang('application_id'); ?></th>
            <th><?php echo $app->lang('application_name'); ?></th>
            <th><?php echo $app->lang('application_secret'); ?></th>
            <th><?php echo $app->lang('creator'); ?></th>
            <th><?php echo $app->lang('application_web_site'); ?></th>
            <th><?php echo $app->lang('server_ip_white_list'); ?></th>
            <th><?php echo $app->lang('usability'); ?></th>
            <th><?php echo $app->lang('create_time'); ?></th>
            <th><?php echo $app->lang('operation'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data_list as $item): ?>
        <tr _id="<?php echo $item['app_id']; ?>" <?php echo $item['status']==0?' class="text-gray"':''; ?>>
            <td><?php echo $item['app_id']; ?></td>
            <td><?php echo $item['app_name']; ?></td>
            <td>
                <a href="/application/show_secret" class="show_title show_secret" title="<?php echo $app->lang('click_to_view_secret'); ?>">
                    <i class="fa fa-eye" aria-hidden="true"></i>
                </a>
                <a href="/application/refresh_secret" class="show_title refresh_secret ml-3" title="<?php echo $app->lang('regenerate_secret'); ?>">
                    <i class="fa fa-refresh" aria-hidden="true"></i>
                </a>
            </td>
            <td>
                <?php if($item['uid']){ ?>
                <a href="/user/detail/<?php echo $item['uid']; ?>" class="user_detail" uid="<?php echo $item['uid']; ?>">
                    <?php echo $item['nickname']; ?>
                </a>
                <?php }else{ ?>
                    <?php echo $item['nickname']; ?>
                <?php } ?>
            </td>
            <td>
                <?php if(!empty($item['index_url'])): ?>
                <a href="<?php echo htmlspecialchars($item['index_url'],ENT_QUOTES); ?>" class="show_title index_url" title="<?php echo htmlspecialchars($item['index_url'],ENT_QUOTES); ?>" target="_blank">
                    <i class="fa fa-link" aria-hidden="true"></i>
                </a>
                <?php endif; ?>
            </td>
            <td><?php echo preg_replace('/[\r\n]+/','<br>', $item['app_white_ip']); ?></td>
            <td>
                <?php if (empty($item['is_fixed'])){ ?>
                <select name="status" last_value="<?php echo $item['status']; ?>">
                    <option value="0"<?php echo $item['status']=='0'? ' selected':''; ?>>
                        <?php echo $app->lang('application_status_0'); ?>
                    </option>
                    <option value="1"<?php echo $item['status']=='1'? ' selected':''; ?>>
                        <?php echo $app->lang('application_status_1'); ?>
                    </option>
                </select>
                <?php }else{ ?>
                    <?php echo $app->lang('application_status_'.$item['status']); ?>
                <?php } ?>
            </td>
            <td><?php echo date('Y-m-d H:i:s', $item['ctime']); ?></td>
            <td>
                <a class="show_title ajax_main_content mr-2" href="/application/permission_list/<?php echo $item['app_id']; ?>" title="<?php echo $app->lang('manage_permission'); ?>">
                    <i class="fa fa-cubes" aria-hidden="true"></i>
                </a>
                <?php if (empty($item['is_fixed'])){ ?>
                    <a class="ajax_main_content mr-1" href="/application/edit/<?php echo $item['app_id']; ?>">
                        <i class="fa fa-edit" aria-hidden="true"></i>
                    </a>
                    <a href="/application/delete" class="delete"><i class="fa fa-close"></i></a>
                <?php } ?>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if(empty($data_list)): ?>
            <tr>
                <td colspan="9" class="pt-5 pb-5"><center><?php echo $app->lang('no_data'); ?></center></td>
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

<!--#include virtual="/include/js_config_js.shtml"-->
<!--#include virtual="/include/js_jsencrypt.shtml"-->
<!--#include virtual="/include/js_application_list.shtml"-->
