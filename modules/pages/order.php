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

if (validate::isNumber($uri->id) && $db->select('id', 'pages', 'id = \'' . $uri->id . '\'', 0, 0, 0, 1) == 1) {
	// Brotkrümelspur setzen
	breadcrumb::assign($lang->t('common', 'acp'), uri('acp'));
	breadcrumb::assign($lang->t('pages', 'pages'), uri('acp/pages'));
	breadcrumb::assign($lang->t('common', 'edit_order'));

	switch ($uri->mode) {
		case 'up':
			if ($db->select('id', 'pages', 'id != \'' . $uri->id . '\' AND left_id < (SELECT left_id FROM ' . CONFIG_DB_PRE . 'pages WHERE id = \'' . $uri->id . '\')', 0, 0, 0, 1) > 0) {
				$elem = $db->select('left_id, right_id', 'pages', 'id = \'' . $uri->id . '\'');
				$pre = $db->select('id, left_id, right_id', 'pages', 'id != \'' . $uri->id . '\' AND left_id < \'' . $elem[0]['left_id'] . '\'', 'left_id DESC', 1);
			} else {
				$error = true;
			}
			break;
		case 'down':
			if ($db->select('id', 'pages', 'id != \'' . $uri->id . '\' AND left_id > (SELECT left_id FROM ' . CONFIG_DB_PRE . 'pages WHERE id = \'' . $uri->id . '\')', 0, 0, 0, 1) > 0) {
				$elem = $db->select('left_id, right_id', 'pages', 'id = \'' . $uri->id . '\'');
				$pre = $db->select('id, left_id, right_id', 'pages', 'id != \'' . $uri->id . '\' AND left_id > \'' . $elem[0]['left_id'] . '\'', 'left_id ASC', 1);
			} else {
				$error = true;
			}
			break;
		default:
				$error = true;
	}
	// Sortierung aktualisieren
	if (!isset($error)) {
		$bool = $db->update('pages', array('left_id' => $pre[0]['left_id'], 'right_id' => $pre[0]['right_id']), 'id = \'' . $uri->id . '\'');
		$bool2 = $db->update('pages', array('left_id' => $elem[0]['left_id'], 'right_id' => $elem[0]['right_id']), 'id = \'' . $pre[0]['id'] . '\'');

		$content = comboBox($bool && $bool2 ? $lang->t('common', 'order_success') : $lang->t('common', 'order_error'), uri('acp/pages'));
		setNavbarCache();
	}
}
if (isset($error)) {
	redirect('errors/404');
}
?>