<?php
/**
 * Files
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_FRONTEND'))
	exit;

if (!empty($modules->cat)) {
	$breadcrumb->assign(lang('files', 'files'), uri('files'));
	$category = $db->select('name', 'categories', 'id = \'' . $modules->cat . '\'');
	$breadcrumb->assign($category[0]['name']);
	$date = ' AND (start = end AND start <= \'' . date_aligned(2, time()) . '\' OR start != end AND start <= \'' . date_aligned(2, time()) . '\' AND end >= \'' . date_aligned(2, time()) . '\')';

	$files = $db->select('id, start, file, size, link_title', 'files', 'category_id = \'' . $modules->cat . '\'' . $date, 'start DESC, end DESC');
	$c_files = count($files);

	if ($c_files > 0) {
		for ($i = 0; $i < $c_files; $i++) {
			$files[$i]['size'] = !empty($files[$i]['size']) ? $files[$i]['size'] : lang('files', 'unknown_filesize');
			$files[$i]['date'] = date_aligned(1, $files[$i]['start']);
		}
		$tpl->assign('files', $files);
	}
	$content = $tpl->fetch('files/files.html');
} else {
	redirect('errors/404');
}
?>