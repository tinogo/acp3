<?php
/**
 * Users
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_ADM'))
	exit;

if (isset($_POST['form'])) {
	$form = $_POST['form'];

	if (!isset($form['language_override']) || $form['language_override'] != 1 && $form['language_override'] != 0)
		$errors[] = $lang->t('users', 'select_languages_override');
	if (!isset($form['entries_override']) || $form['entries_override'] != 1 && $form['entries_override'] != 0)
		$errors[] = $lang->t('users', 'select_entries_override');

	if (isset($errors)) {
		$tpl->assign('error_msg', comboBox($errors));
	} else {
		$bool = config::module('users', $form);

		$content = comboBox($bool ? $lang->t('common', 'settings_success') : $lang->t('common', 'settings_error'), uri('acp/users'));
	}
}
if (!isset($_POST['form']) || isset($errors) && is_array($errors)) {
	$settings = config::getModuleSettings('users');

	$languages[0]['value'] = '1';
	$languages[0]['checked'] = selectEntry('language_override', '1', $settings['language_override'], 'checked');
	$languages[0]['lang'] = $lang->t('common', 'yes');
	$languages[1]['value'] = '0';
	$languages[1]['checked'] = selectEntry('language_override', '0', $settings['language_override'], 'checked');
	$languages[1]['lang'] = $lang->t('common', 'no');
	$tpl->assign('languages', $languages);

	$entries[0]['value'] = '1';
	$entries[0]['checked'] = selectEntry('entries_override', '1', $settings['entries_override'], 'checked');
	$entries[0]['lang'] = $lang->t('common', 'yes');
	$entries[1]['value'] = '0';
	$entries[1]['checked'] = selectEntry('entries_override', '0', $settings['entries_override'], 'checked');
	$entries[1]['lang'] = $lang->t('common', 'no');
	$tpl->assign('entries', $entries);

	$content = modules::fetchTemplate('users/settings.html');
}