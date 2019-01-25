<?php
/**
 * The Pagination class
 */
abstract class Pagination
{
	/**
	 * 生成链接
	 *
	 * @param Array		$args	分页参数
	 * @param String	$module	模块名
	 * @param String	$action	方法名
	 * @param Array		$appends	附加的参数
	 * @param String	$type	链接类型
	 * @return String
	 */
	static public function textual($args, $module, $action, $appends = array(), $type = 'wml') {
		$pagination = self::generate($args);

		if ($type == 'wml') {
			$func = 'linkwml';
		} else {
			$func = 'linkhtml';
		}
		$str = '';
		foreach ($pagination as $key => $page) {
			$appends['page'] = $page;
			if ($key == 'previous_page') {
				$str .= "<a href='" . $func($module, $action, $appends) . "'>前一页</a>&nbsp;&nbsp;";
			} else if ($key == 'next_page') {
				$str .= "<a href='" . $func($module, $action, $appends) . "'>下一页</a>&nbsp;&nbsp;";
			} else if ($key == 'last_page') {
				$str .= "<a href='" . $func($module, $action, $appends) . "'>末页</a>&nbsp;&nbsp;";
			} else if ($key == 'first_page') {
				$str .= "<a href='" . $func($module, $action, $appends) . "'>首页</a>&nbsp;&nbsp;";
			} else { //numeric key
				if ($args['current_page'] == $page) {
					$str .= $page . "&nbsp;&nbsp;";
				} else {
				$str .= "<a href='" . $func($module, $action, $appends) . "'>$page</a>&nbsp;&nbsp;";
				}
			}
		}

		return $str;
	}

	/**
	 * Generate the pagination array
	 * 
	 * @param array $args 
	 * @return array
	 */
	static public function generate($args) {
		$paginationArray = array();
		$args['page_size'] = isset($args['page_size']) ? $args['page_size'] : 10;
		$pageTotal = ceil($args['item_total'] / $args['page_size']);
		$displayPages = isset($args['display_pages']) ? $args['display_pages'] : 5;
		if (0 < $pageTotal) {
			$currentPage = $args['current_page'];	
			if ($pageTotal > $displayPages) {
				$firstPage = max($currentPage - floor($displayPages / 2), 1);
				$firstPage = min($firstPage, $pageTotal - $displayPages + 1);
				$lastPage = $firstPage + $displayPages - 1;
			} else {
				$firstPage = 1;
				$lastPage = $pageTotal;
			}
			$paginationArray["first_page"] = $firstPage;
			if (1 < $currentPage) {
				$paginationArray["previous_page"] = $currentPage - 1;
			}
			for ($i = $firstPage; $i <= $lastPage; $i++) {
				$paginationArray[$i] = $i;
			}
			if ($pageTotal > $currentPage) {
				$paginationArray["next_page"] = $currentPage + 1;
			}
			$paginationArray["last_page"] = $lastPage;
		}
		return $paginationArray;
	}
}
