<?php
/**
 * Guestbook
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit;

$emoticons_active = modules::isActive('emoticons');
$newsletter_active = modules::isActive('newsletter');

if (isset($_POST['form']) === true) {
	$form = $_POST['form'];

	if (empty($form['dateformat']) || ($form['dateformat'] !== 'long' && $form['dateformat'] !== 'short'))
		$errors['dateformat'] = $lang->t('common', 'select_date_format');
	if (!isset($form['notify']) || ($form['notify'] != 0 && $form['notify'] != 1 && $form['notify'] != 2))
		$errors['notify'] = $lang->t('guestbook', 'select_notification_type');
	if ($form['notify'] != 0 && validate::email($form['notify_email']) === false)
		$errors['notify-email'] = $lang->t('common', 'wrong_email_format');
	if (!isset($form['overlay']) || $form['overlay'] != 1 && $form['overlay'] != 0)
		$errors[] = $lang->t('guestbook', 'select_use_overlay');
	if ($emoticons_active === true && (!isset($form['emoticons']) || ($form['emoticons'] != 0 && $form['emoticons'] != 1)))
		$errors[] = $lang->t('guestbook', 'select_emoticons');
	if ($newsletter_active === true && (!isset($form['newsletter_integration']) || ($form['newsletter_integration'] != 0 && $form['newsletter_integration'] != 1)))
		$errors[] = $lang->t('guestbook', 'select_newsletter_integration');

	if (isset($errors) === true) {
		$tpl->assign('error_msg', errorBox($errors));
	} elseif (validate::formToken() === false) {
		view::setContent(errorBox($lang->t('common', 'form_already_submitted')));
	} else {
		$bool = config::module('guestbook', $form);

		$session->unsetFormToken();

		setRedirectMessage($bool === true ? $lang->t('common', 'settings_success') : $lang->t('common', 'settings_error'), 'acp/guestbook');
	}
}
if (isset($_POST['form']) === false || isset($errors) === true && is_array($errors) === true) {
	$settings = config::getModuleSettings('guestbook');

	$tpl->assign('dateformat', $date->dateformatDropdown($settings['dateformat']));

	$notify = array();
	$notify[0]['value'] = '0';
	$notify[0]['selected'] = selectEntry('notify', '0', $settings['notify']);
	$notify[0]['lang'] = $lang->t('guestbook', 'no_notification');
	$notify[1]['value'] = '1';
	$notify[1]['selected'] = selectEntry('notify', '1', $settings['notify']);
	$notify[1]['lang'] = $lang->t('guestbook', 'notify_on_new_entry');
	$notify[2]['value'] = '2';
	$notify[2]['selected'] = selectEntry('notify', '2', $settings['notify']);
	$notify[2]['lang'] = $lang->t('guestbook', 'notify_and_enable');
	$tpl->assign('notify', $notify);

	$overlay = array();
	$overlay[0]['value'] = '1';
	$overlay[0]['checked'] = selectEntry('overlay', '1', $settings['overlay'], 'checked');
	$overlay[0]['lang'] = $lang->t('common', 'yes');
	$overlay[1]['value'] = '0';
	$overlay[1]['checked'] = selectEntry('overlay', '0', $settings['overlay'], 'checked');
	$overlay[1]['lang'] = $lang->t('common', 'no');
	$tpl->assign('overlay', $overlay);

	// Emoticons erlauben
	if ($emoticons_active === true) {
		$allow_emoticons = array();
		$allow_emoticons[0]['value'] = '1';
		$allow_emoticons[0]['checked'] = selectEntry('emoticons', '1', $settings['emoticons'], 'checked');
		$allow_emoticons[0]['lang'] = $lang->t('common', 'yes');
		$allow_emoticons[1]['value'] = '0';
		$allow_emoticons[1]['checked'] = selectEntry('emoticons', '0', $settings['emoticons'], 'checked');
		$allow_emoticons[1]['lang'] = $lang->t('common', 'no');
		$tpl->assign('allow_emoticons', $allow_emoticons);
	}

	// In Newsletter integrieren
	if ($newsletter_active === true) {
		$newsletter_integration = array();
		$newsletter_integration[0]['value'] = '1';
		$newsletter_integration[0]['checked'] = selectEntry('newsletter_integration', '1', $settings['newsletter_integration'], 'checked');
		$newsletter_integration[0]['lang'] = $lang->t('common', 'yes');
		$newsletter_integration[1]['value'] = '0';
		$newsletter_integration[1]['checked'] = selectEntry('newsletter_integration', '0', $settings['newsletter_integration'], 'checked');
		$newsletter_integration[1]['lang'] = $lang->t('common', 'no');
		$tpl->assign('newsletter_integration', $newsletter_integration);
	}

	$tpl->assign('form', isset($form) ? $form : array('notify_email' => $settings['notify_email']));

	$session->generateFormToken();

	view::setContent(view::fetchTemplate('guestbook/settings.tpl'));
}