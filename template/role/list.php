<!--{use_layout layout/main}-->
<?php
$head_info = [
    'title' =>  $app->lang('user_role'),
];
?>

<form class="needs-validation" novalidate>
    <div class="row">
        <div class="col-md-5 mb-2">
            <input type="text" class="form-control" name="role_name" placeholder="<?php echo $app->lang('role_name'); ?>" value="<?php echo isset($_REQUEST['role_name'])? $_REQUEST['role_name']:''; ?>">
        </div>
        <div class="col-md-7 mb-2">
            <button class="btn btn-primary btn-sm pl-5 pr-5 mb-2" type="submit"><?php echo $app->lang('search'); ?></button>
            <button class="btn btn-primary btn-sm ml-4 mb-2" type="button" id="clear_form"><?php echo $app->lang('clean_up'); ?></button>
            <select class="ml-4 mb-2" name="page_size">
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
            <a href="/role/add" class="btn btn-sm btn-outline-primary ml-4 mb-2 ajax_main_content">
                <i class="fa fa-plus" aria-hidden="true"></i>
                <?php echo $app->lang('create_role'); ?>
            </a>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-sm table_list" id="all_role_list">
        <thead>
        <tr>
            <th>ID</th>
            <th><?php echo $app->lang('translation'); ?></th>
            <th><?php echo $app->lang('role_name'); ?></th>
            <th><?php echo $app->lang('description'); ?></th>
            <th><?php echo $app->lang('operation'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($data_list as $item): ?>
        <tr _id="<?php echo $item['id']; ?>">
            <td <?php echo count($data_list)>1?'':'class="pb-5"'; ?>><?php echo $item['id']; ?></td>
            <td><?php echo $app->lang($item['role_name']); ?></td>
            <td><?php echo $item['role_name']; ?></td>
            <td><?php echo $item['description']; ?></td>
            <td>
                <a class="show_title show_users mr-2" href="/role/users/<?php echo $item['id']; ?>" title="<?php echo $app->lang('view_people_with_this_role'); ?>">
                    <i class="fa fa-users" aria-hidden="true"></i>
                </a>
                <a class="show_title ajax_main_content mr-2" href="/role/grant_permission/<?php echo $item['id']; ?>" title="<?php echo $app->lang('to_grant_authorization'); ?>">
                    <i class="fa fa-cubes" aria-hidden="true"></i>
                </a>
                <a class="ajax_main_content mr-1" href="/role/edit/<?php echo $item['id']; ?>">
                    <i class="fa fa-edit" aria-hidden="true"></i>
                </a>
                <a href="/role/delete" class="delete"><i class="fa fa-close"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if(empty($data_list)): ?>
            <tr>
                <td colspan="5" class="pt-5 pb-5"><center><?php echo $app->lang('no_data'); ?></center></td>
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

<!--#include virtual="/include/js_role_list.shtml"-->
