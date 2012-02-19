<?php
/**
 * Pages
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit;

if (validate::isNumber($uri->id) === true && $db->countRows('*', 'menu_items_blocks', 'id = \'' . $uri->id . '\'') == 1) {
	require_once MODULES_DIR . 'menu_items/functions.php';

	$breadcrumb->append($lang->t('menu_items', 'adm_list_blocks'), $uri->route('acp/menu_items/adm_list_blocks'))
			   ->append($lang->t('menu_items', 'edit_block'));

	if (isset($_POST['form']) === true) {
		$form = $_POST['form'];

		if (!preg_match('/^[a-zA-Z]+\w/', $form['index_name']))
			$errors['index-name'] = $lang->t('menu_items', 'type_in_index_name');
		if (!isset($errors) && $db->countRows('*', 'menu_items_blocks', 'index_name = \'' . $db->escape($form['index_name']) . '\' AND id != \'' . $uri->id . '\'') > 0)
			$errors['index-name'] = $lang->t('menu_items', 'index_name_unique');
		if (strlen($form['title']) < 3)
			$errors['title'] = $lang->t('menu_items', 'block_title_to_short');

		if (isset($errors) === true) {
			$tpl->assign('error_msg', errorBox($errors));
		} elseif (validate::formToken() === false) {
			view::setContent(errorBox($lang->t('common', 'form_already_submitted')));
		} else {
			$update_values = array(
				'index_name' => $db->escape($form['index_name']),
				'title' => $db->escape($form['title']),
			);

			$bool = $db->update('menu_items_blocks', $update_values, 'id = \'' . $uri->id . '\'');

			setMenuItemsCache();

			$session->unsetFormToken();

			setRedirectMessage($bool !== false ? $lang->t('common', 'edit_success') : $lang->t('common', 'edit_error'), 'acp/menu_items/adm_list_blocks');
		}
	}
	if (isset($_POST['form']) === false || isset($errors) === true && is_array($errors) === true) {
		$block = $db->select('index_name, title', 'menu_items_blocks', 'id = \'' . $uri->id . '\'');
		$block[0]['index_name'] = $db->escape($block[0]['index_name'], 3);
		$block[0]['title'] = $db->escape($block[0]['title'], 3);

		$tpl->assign('form', isset($form) ? $form : $block[0]);

		$session->generateFormToken();

		view::setContent(view::fetchTemplate('menu_items/edit_block.tpl'));
	}
} else {
	$uri->redirect('errors/404');
}
