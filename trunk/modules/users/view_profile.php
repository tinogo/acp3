<?php
if (!defined('IN_ACP3'))
	exit;

breadcrumb::assign($lang->t('users', 'users'), $uri->route('users'));
breadcrumb::assign($lang->t('users', 'view_profile'));

if (validate::isNumber($uri->id) && $db->countRows('*', 'users', 'id = \'' . $uri->id . '\'') == '1') {
	$user = $auth->getUserInfo($uri->id);
	$user['gender'] = str_replace(array(1, 2, 3), array('-', $lang->t('users', 'female'), $lang->t('users', 'male')), $user['gender']);
	$user['birthday'] = $date->format($user['birthday'], $user['birthday_format'] == 1 ? 'd.m.Y' : 'd.m');
	$tpl->assign('user', $user);
}
$content = modules::fetchTemplate('users/view_profile.html');
