<?php
/*
 * 分页码类
 * YiluPHP vision 2.0
 * User: Jim.Wu
 * * Date: 2021/01/23
 * Time: 09:22
 */

class pager extends base_class
{

	/**
	 * @name 组装分页码
	 * @desc 返回组装好的页码数组
	 * @param integer $count 总记录数
	 * @param integer $page 当前页，默认为当前的page参数值，默认为1
	 * @param integer $page_size 每页显示条数，默认为当前的page_size参数值，默认为10
	 * @param integer $button_num 同时最多显示的页码数，默认为10
	 * @return array
	 */
	public function build_pages_data($count, $page, $page_size, $button_num=10)
	{
		$page_num = ceil($count/$page_size);
		$min_page = $page - ceil($button_num/2);
		$min_page = $min_page<1 ? 1 : $min_page;
		$max_page = $min_page + $button_num - 1;
		if($max_page>$page_num){
			$max_page = $page_num;
			//再次纠正前面的页码
			$min_page = $max_page-$button_num;
			$min_page = $min_page<1 ? 1 : $min_page;
		}
		$pages = [
				'total' => $count,
				'page_size' => $page_size,
				'page' => $page,
				'total_page' => $page_num,
				'first_page' => 1,
				'pre_page' => $page-1<1 ? null : $page-1,
				'next_page' => $page+1>$page_num ? null : $page+1,
				'last_page' => $page_num,
				'page_list' => [],
		];
		for($i=$min_page; $i<=$max_page; $i++){
			$pages['page_list'][] = $i;
		}
		return $pages;
	}

	/**
	 * @name 获取当前完整的URL
	 * @desc 会判断是HTTP还是HTTPS
	 * @return string
	 */
	public function get_url()
	{
		$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
		return $http_type . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	/**
	 * @name 显示或返回分页码的HTML
	 * @desc 返回组装好的页码数组
	 * @param array $param 总记录数
	 * data_count 数据条数的总数量，必填
	 * page 当前页码，默认为：1
	 * page_size 当前每页数量，默认为：10
	 * show_button_num 最多同时显示多少个数字的页码，默认为：10
	 * page_key 页码的参数名，默认为：page
	 * page_size_key 每页数量的参数名，默认为：page_size
	 * base_url 连接地址，在此地址上添加页码和每页数量参数，如果页码和每页数量包含在url的路径中，则使用占位符{page}和{page_size}填写入路径中，默认为当前URL
	 * class_name 页码DOM对象UL的样式名称，通过此样式的名称控制整个页码的样式，默认为“pages”
	 * first_page_text 首页的显示文字，默认为“首页”
	 * pre_page_text 上一页的显示文字，默认为“上一页”
	 * next_page_text 下一页的显示文字，默认为“下一页”
	 * last_page_text 尾页的显示文字，默认为“尾页”
	 * a_class 给a链接设置的样式名，默认为没有
	 * @param boolean $return_html false为直接打印输出HTML，true为返回html字符串
	 * @return string
	 */
	public function display_pages($param, $return_html=false)
	{
		if(!isset($param['data_count'])){
			show_info('lack arguments $param[\'data_count\']');
		}
		$html = '';
		!isset($param['page_key']) && $param['page_key']='page';
		!isset($param['page_size_key']) && $param['page_size_key']='page_size';
		!isset($param['page']) && $param['page']=1;
		!isset($param['page_size']) && $param['page_size']=10;
		!isset($param['show_button_num']) && $param['show_button_num']=10;
		if(!isset($param[$param['page_size_key']]) ){
			if(isset($_GET[$param['page_size_key']]) ){
				$param[$param['page_size_key']] = intval($_GET[$param['page_size_key']]);
				$param[$param['page_size_key']] = $param[$param['page_size_key']]<=0 ? '':$param[$param['page_size_key']];
			}
			if(empty($param[$param['page_size_key']])){
				$param[$param['page_size_key']]=null;
			}
		}
		if(!isset($param[$param['page_key']]) ){
			if(isset($_GET[$param['page_key']]) ){
				$param[$param['page_key']] = intval($_GET[$param['page_key']]);
				$param[$param['page_key']] = $param[$param['page_key']]<=0 ? '':$param[$param['page_key']];
			}
			if(empty($param[$param['page_key']])){
				$param[$param['page_key']]=1;
			}
		}
		$page_data = $this->build_pages_data($param['data_count'], $param['page'], $param['page_size'], $param['show_button_num']);
		$param = array_merge($param, $page_data);

		if($param['total_page']>1){
			!isset($param['base_url']) && $param['base_url']=$this->get_url();
			!isset($param['class_name']) && $param['class_name']='pages';
			!isset($param['first_page_text']) && $param['first_page_text']='首页';
			!isset($param['pre_page_text']) && $param['pre_page_text']='上一页';
			!isset($param['next_page_text']) && $param['next_page_text']='下一页';
			!isset($param['last_page_text']) && $param['last_page_text']='尾页';
			$build_url = function($page) use ($param){
				$url = parse_url($param['base_url']);
				$query = isset($url['query'])?$url['query']:'';
				$url = $url['scheme'].'://'.$url['host'].$url['path'];
				//查看页码是否包含在url的路径中
				if($page_in_path = preg_match("/\{page\}/",$url)){
					//替换之
					$url = str_replace('{page}', $page, $url);
				}
				//查看每页数量是否包含在url的路径中
				if($page_size_in_path = preg_match("/\{page_size\}/",$url)){
					//替换之
					$url = str_replace('{page_size}', $param['page_size'], $url);
				}

				if($query){
					$query = explode('&', $query);
				}
				else{
					$query = [];
				}
				$tmp = [];
				foreach ($query as $value) {
					$t = explode('=', $value);
					if(in_array(strtolower($t[0]), ['dtype','with_layout']) || strpos(strtolower($t[0]), 'pager_ignore_')===0){
						continue;
					}
					if(!in_array(strtolower($t[0]), [$param['page_key'], $param['page_size_key']])){
						$tmp[] = $t[0].'='.(isset($t[1])?$t[1]:'');
					}
				}
				//在参数中设置页码
				if(!$page_in_path){
					$tmp[] = $param['page_key'].'='.$page;
				}

				//在参数中设置每页数量
				if(!$page_size_in_path && !empty($_REQUEST[$param['page_size_key']]) && !empty($param[$param['page_size_key']])){
					$tmp[] = $param['page_size_key'].'='.$param[$param['page_size_key']];
				}
				return $url.($tmp?'?'.implode('&',$tmp):'');
			};
			$url = '';
			$html = '<ul class="'.$param['class_name'].'"><li class="first '.($param['page']>1 ? '':'invalid').'"><a href="'.$build_url(1).'"'.(empty($param['a_class'])?'':' class="'.$param['a_class'].'"').'>'.$param['first_page_text'].'</a></li><li class="pre '.($param['page']>1 ? '':'invalid').'"><a href="'.($param['pre_page']?$build_url($param['pre_page']):'javascript:void(0)').'"'.(empty($param['a_class'])?'':' class="'.$param['a_class'].'"').'>'.$param['pre_page_text'].'</a></li>';
			foreach ($param['page_list'] as $the_page){
				$html .= '<li class="item'.($param['page']==$the_page ? ' current' : '').'"><a href="'.$build_url($the_page).'"'.(empty($param['a_class'])?'':' class="'.$param['a_class'].'"').'>'.$the_page.'</a></li>';
			}
			$html .= '<li class="next '.($param['page']<$param['total_page'] ? '':'invalid').'"><a href="'.($param['next_page']?$build_url($param['next_page']):'javascript:void(0)').'"'.(empty($param['a_class'])?'':' class="'.$param['a_class'].'"').'>'.$param['next_page_text'].'</a></li><li class="last '.($param['page']<$param['total_page'] ? '':'invalid').'"><a href="'.$build_url($param['last_page']).'"'.(empty($param['a_class'])?'':' class="'.$param['a_class'].'"').'>'.$param['last_page_text'].'</a></li></ul>';
		}
		if ($return_html) {
			return $html;
		}
		echo $html;
	}
}
