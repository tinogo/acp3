<?php
/**
 * Pages
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit();

if (isset($_POST['entries']) && is_array($_POST['entries']))
	$entries = $_POST['entries'];
elseif (validate::deleteEntries($uri->entries))
	$entries = $uri->entries;

if (!isset($entries)) {
	view::setContent(comboBox($lang->t('common', 'no_entries_selected')));
} elseif (is_array($entries)) {
	$marked_entries = implode('|', $entries);
	view::setContent(comboBox($lang->t('common', 'confirm_delete'), $uri->route('acp/menu_items/delete/entries_' . $marked_entries . '/action_confirmed/'), $uri->route('acp/menu_items')));
} elseif (validate::deleteEntries($entries) && $uri->action == 'confirmed') {
	require_once MODULES_DIR . 'menu_items/functions.php';

	$marked_entries = explode('|', $entries);
	$bool = null;
	foreach ($marked_entries as $entry) {
		// URI-Alias löschen
		$menu_item = $db->select('uri', 'menu_items', 'id = \'' . $entry . '\'');
		seo::deleteUriAlias($menu_item[0]['uri']);

		$bool = menuItemsDeleteNode($entry);
	}
	setMenuItemsCache();

	setRedirectMessage($bool !== null ? $lang->t('common', 'delete_success') : $lang->t('common', 'delete_error'), 'acp/menu_items');
} else {
	$uri->redirect('acp/errors/404');
}
