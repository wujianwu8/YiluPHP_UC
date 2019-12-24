<?php if($page_data['total_page']>1): ?>
<ul class="pages">
	<li class="first <?php echo $page_data['page']>1 ? '':'invalid'; ?>"><a href="/user/list/page_size/<?php echo $page_data['page_size']; ?>/page/1">首页</a></li>
	<li class="pre <?php echo $page_data['page']>1 ? '':'invalid'; ?>"><a href="/user/list/page_size/<?php echo $page_data['page_size']; ?>/page/<?php echo $page_data['pre_page']; ?>">上一页</a></li>
	<?php foreach ($page_data['page_list'] as $value): ?>
	<li class="item<?php echo $page_data['page']==$value ? ' current' : ''; ?>"><a href="/user/list/page_size/<?php echo $page_data['page_size']; ?>/page/<?php echo $value; ?>"><?php echo $value; ?></a></li>
	<?php endforeach; ?>
	<li class="next <?php echo $page_data['page']<$page_data['total_page'] ? '':'invalid'; ?>"><a href="/user/list/page_size/<?php echo $page_data['page_size']; ?>/page/<?php echo $page_data['next_page']; ?>">下一页</a></li>
	<li class="last <?php echo $page_data['page']<$page_data['total_page'] ? '':'invalid'; ?>"><a href="/user/list/page_size/<?php echo $page_data['page_size']; ?>/page/<?php echo $page_data['total_page']; ?>">尾页</a></li>
</ul>
<?php endif; ?>