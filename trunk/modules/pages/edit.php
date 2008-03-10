<?php
/**
 * Pages
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_ADM'))
	exit;

if (!empty($modules->id) && $db->select('id', 'pages', 'id = \'' . $modules->id . '\'', 0, 0, 0, 1) == '1') {
	if (isset($_POST['submit'])) {
		include_once 'modules/pages/functions.php';
		$form = $_POST['form'];

		if (!$validate->date($form))
			$errors[] = lang('common', 'select_date');
		if (!$validate->is_number($form['mode']))
			$errors[] = lang('pages', 'select_static_hyperlink');
		if (!$validate->is_number($form['blocks']))
			$errors[] = lang('pages', 'select_block');
		if (!empty($form['blocks']) && !$validate->is_number($form['sort']))
			$errors[] = lang('pages', 'type_in_chronology');
		if (strlen($form['title']) < 3)
			$errors[] = lang('pages', 'title_to_short');
		if ($form['mode'] == '1' && !empty($form['parent']) && !$validate->is_number($form['parent']))
			$errors[] = lang('pages', 'select_superior_page');
		if ($form['mode'] == '1' && $validate->is_number($form['parent']) && ($db->select('id', 'pages', "id != '" . $modules->id . "' AND mode='1' AND parent='0'", 0, 0, 0, 1) == 0) || $form['parent'] == $modules->id || parent_check($modules->id, $form['parent']))
			$errors[] = lang('pages', 'superior_page_not_allowed');
		if ($form['mode'] == '1' && strlen($form['text']) < 3)
			$errors[] = lang('pages', 'text_to_short');
		if (($form['mode'] == '2' || $form['mode'] == '3') && (empty($form['uri']) || !$validate->is_number($form['target'])))
			$errors[] = lang('pages', 'type_in_uri_and_target');

		if (isset($errors)) {
			combo_box($errors);
		} else {
			$start_date = date_aligned(3, array($form['start_hour'], $form['start_min'], 0, $form['start_month'], $form['start_day'], $form['start_year']));
			$end_date = date_aligned(3, array($form['end_hour'], $form['end_min'], 0, $form['end_month'], $form['end_day'], $form['end_year']));

			if ($form['mode'] == '1') {
				$form['uri'] = '';
				$form['target'] = '';
			} else {
				$form['parent'] = '';
				$form['text'] = '';
			}

			$update_values = array(
				'start' => $start_date,
				'end' => $end_date,
				'mode' => $form['mode'],
				'parent' => $form['parent'],
				'block_id' => $form['blocks'],
				'sort' => $form['sort'],
				'title' => $db->escape($form['title']),
				'uri' => $db->escape($form['uri'], 2),
				'target' => $form['target'],
				'text' => $db->escape($form['text'], 2),
			);

			$bool = $db->update('pages', $update_values, 'id = \'' . $modules->id . '\'');

			$cache->create('pages', $db->select('p.id, p.start, p.end, p.mode, p.title, p.uri, p.target, b.index_name AS block_name', 'pages AS p, ' . CONFIG_DB_PRE . 'pages_blocks AS b', 'p.block_id != \'0\' AND p.block_id = b.id', 'p.sort ASC, p.title ASC'));
			$cache->create('pages_list_id_' . $modules->id, $db->select('mode, uri, text', 'pages', 'id = \'' . $modules->id . '\''));

			$content = combo_box($bool ? lang('pages', 'edit_success') : lang('pages', 'edit_error'), uri('acp/pages'));
		}
	}
	if (!isset($_POST['submit']) || isset($errors) && is_array($errors)) {
		//Funktionen einbinden
		include_once 'modules/pages/functions.php';

		$page = $db->select('start, end, mode, parent, block_id, sort, title, uri, target, text', 'pages', 'id = \'' . $modules->id . '\'');
		$page[0]['text'] = $db->escape($page[0]['text'], 3);
		$page[0]['uri'] = $db->escape($page[0]['uri'], 3);

		// Datumsauswahl
		$tpl->assign('start_date', publication_period('start', $page[0]['start']));
		$tpl->assign('end_date', publication_period('end', $page[0]['end']));

		$mode[0]['value'] = 1;
		$mode[0]['selected'] = select_entry('mode', '1', $page[0]['mode']);
		$mode[0]['lang'] = lang('pages', 'static_page');
		$mode[1]['value'] = 2;
		$mode[1]['selected'] = select_entry('mode', '2', $page[0]['mode']);
		$mode[1]['lang'] = lang('pages', 'dynamic_page');
		$mode[2]['value'] = 3;
		$mode[2]['selected'] = select_entry('mode', '3', $page[0]['mode']);
		$mode[2]['lang'] = lang('pages', 'hyperlink');
		$tpl->assign('mode', $mode);

		$blocks = $db->select('id, title', 'pages_blocks', 0, 'title ASC, id ASC');
		$c_blocks = count($blocks);

		for ($i = 0; $i < $c_blocks; $i++) {
			$blocks[$i]['selected'] = select_entry('blocks', $blocks[$i]['id'], $page[0]['block_id']);
		}
		$blocks[$c_blocks]['id'] = '0';
		$blocks[$c_blocks]['index_name'] = 'dot_display';
		$blocks[$c_blocks]['selected'] = select_entry('block', '0', $page[0]['block_id']);
		$blocks[$c_blocks]['title'] = lang('pages', 'do_not_display');
		$tpl->assign('blocks', $blocks);

		$target[0]['value'] = 1;
		$target[0]['selected'] = select_entry('target', '1', $page[0]['target']);
		$target[0]['lang'] = lang('common', 'window_self');
		$target[1]['value'] = 2;
		$target[1]['selected'] = select_entry('target', '2', $page[0]['target']);
		$target[1]['lang'] = lang('common', 'window_blank');
		$tpl->assign('target', $target);

		$tpl->assign('form', isset($form) ? $form : $page[0]);

		$tpl->assign('pages_list', pages_list(0, $page[0]['parent']));

		$content = $tpl->fetch('pages/edit.html');
	}
} else {
	redirect('errors/404');
}
?>