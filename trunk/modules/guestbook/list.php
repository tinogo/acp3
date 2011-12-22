<?php
/**
 * Guestbook
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_ACP3'))
	exit;

$settings = config::getModuleSettings('guestbook');

$guestbook = $db->query('SELECT u.nickname AS user_name, u.website AS user_website, u.mail AS user_mail, g.id, g.date, g.name, g.user_id, g.message, g.website, g.mail FROM {pre}guestbook AS g LEFT JOIN {pre}users AS u ON(u.id = g.user_id) ' . ($settings['notify'] == 2 ? 'WHERE active = 1' : '') . ' ORDER BY date DESC LIMIT ' . POS . ', ' . $auth->entries);
$c_guestbook = count($guestbook);

if ($c_guestbook > 0) {
	$tpl->assign('pagination', pagination($db->countRows('*', 'guestbook')));

	// Emoticons einbinden
	$emoticons = modules::check('emoticons', 'functions') == 1 && $settings['emoticons'] == 1 ? true : false;
	if ($emoticons) {
		require_once MODULES_DIR . 'emoticons/functions.php';
	}

	for ($i = 0; $i < $c_guestbook; ++$i) {
		if (empty($guestbook[$i]['user_name']) && empty($guestbook[$i]['name'])) {
			$guestbook[$i]['name'] = $lang->t('users', 'deleted_user');
			$guestbook[$i]['user_id'] = 0;
		}
		$guestbook[$i]['name'] = !empty($guestbook[$i]['user_name']) ? $guestbook[$i]['user_name'] : $guestbook[$i]['name'];
		$guestbook[$i]['date'] = $date->format($guestbook[$i]['date'], $settings['dateformat']);
		$guestbook[$i]['message'] = str_replace(array("\r\n", "\r", "\n"), '<br />', $guestbook[$i]['message']);
		if ($emoticons) {
			$guestbook[$i]['message'] = emoticonsReplace($guestbook[$i]['message']);
		}
		$guestbook[$i]['website'] = strlen($guestbook[$i]['user_website']) > 2 ? substr($guestbook[$i]['user_website'], 0, -2) : $guestbook[$i]['website'];
		if (!empty($guestbook[$i]['website']) && strpos($guestbook[$i]['website'], 'http://') === false)
			$guestbook[$i]['website'] = 'http://' . $guestbook[$i]['website'];

		$guestbook[$i]['mail'] = !empty($guestbook[$i]['user_mail']) ? substr($guestbook[$i]['user_mail'], 0, -2) : $guestbook[$i]['mail'];
	}
	$tpl->assign('guestbook', $guestbook);
}
$content = modules::fetchTemplate('guestbook/list.html');
