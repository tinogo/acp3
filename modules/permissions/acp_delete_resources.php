<?php
/**
 ** Access Control List
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit;

if (isset($_POST['entries']) && is_array($_POST['entries']) === true)
	$entries = $_POST['entries'];
elseif (ACP3_Validate::deleteEntries(ACP3_CMS::$uri->entries) === true)
	$entries = ACP3_CMS::$uri->entries;

ACP3_CMS::$breadcrumb->append(ACP3_CMS::$lang->t('permissions', 'acp_list_resources'), ACP3_CMS::$uri->route('acp/permissions/acp_list_resources'))
		   ->append(ACP3_CMS::$lang->t('permissions', 'delete_resources'));

if (!isset($entries)) {
	ACP3_CMS::$view->setContent(errorBox(ACP3_CMS::$lang->t('system', 'no_entries_selected')));
} elseif (is_array($entries) === true) {
	$marked_entries = implode('|', $entries);
	ACP3_CMS::$view->setContent(confirmBox(ACP3_CMS::$lang->t('system', 'confirm_delete'), ACP3_CMS::$uri->route('acp/permissions/delete_resources/entries_' . $marked_entries . '/action_confirmed/'), ACP3_CMS::$uri->route('acp/permissions/list_resources')));
} elseif (ACP3_CMS::$uri->action === 'confirmed') {
	$marked_entries = explode('|', $entries);
	$bool = false;

	foreach ($marked_entries as $entry) {
		$bool = ACP3_CMS::$db2->delete(DB_PRE . 'acl_resources', array('id' => $entry));
	}

	ACP3_ACL::setResourcesCache();

	setRedirectMessage($bool, ACP3_CMS::$lang->t('system', $bool !== false ? 'delete_success' : 'delete_error'), 'acp/permissions/list_resources');
} else {
	ACP3_CMS::$uri->redirect('errors/404');
}
