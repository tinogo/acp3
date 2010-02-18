<?php
error_reporting(0);

define('IN_INSTALL', true);
define('PHP_SELF', htmlentities($_SERVER['SCRIPT_NAME']));
$php_self = dirname(PHP_SELF);
define('ROOT_DIR', $php_self != '/' ? $php_self . '/' : '/');
define('CONFIG_VERSION', '4.0 SVN');

include ACP3_ROOT . 'includes/globals.php';

set_include_path(get_include_path() . PATH_SEPARATOR . ACP3_ROOT . 'includes/classes/');
spl_autoload_extensions('.class.php');
spl_autoload_register();

include ACP3_ROOT . 'installation/includes/functions.php';

$uri = new uri();

if (empty($uri->query)) {
	$uri->mod = 'install';
	$uri->page = 'welcome';
}
$l = !empty($_POST['lang']) ? $_POST['lang'] : $uri->lang;
define('LANG', !empty($l) && !preg_match('=/=', $l) && is_file(ACP3_ROOT . 'languages/' . $l . '/info.xml') ? $l : 'de');
$lang = new lang();

// Smarty einbinden
include ACP3_ROOT . 'includes/smarty3/Smarty.class.php';
$tpl = new Smarty();
$tpl->template_dir = ACP3_ROOT . 'installation/design/';
$tpl->compile_dir = ACP3_ROOT . 'cache/installation/';
if (!is_dir($tpl->compile_dir)) {
	if (!is_writable(ACP3_ROOT . 'cache/')) {
		exit('Bitte geben Sie dem "cache"-Ordner den CHMOD 777!');
	} else {
		mkdir($tpl->compile_dir, 0777);
	}
}

$tpl->assign('PHP_SELF', PHP_SELF);
$tpl->assign('ROOT_DIR', ROOT_DIR);
$tpl->assign('REQUEST_URI', htmlentities($_SERVER['REQUEST_URI'], ENT_QUOTES));
$tpl->assign('LANG', LANG);

$pages = array(
	array(
		'title' => $lang->t('installation', 'welcome'),
		'page' => 'welcome',
		'selected' => '',
	),
	array(
		'title' => $lang->t('installation', 'licence'),
		'page' => 'licence',
		'selected' => '',
	),
	array(
		'title' => $lang->t('installation', 'requirements'),
		'page' => 'requirements',
		'selected' => '',
	),
	array(
		'title' => $lang->t('installation', 'configuration'),
		'page' => 'configuration',
		'selected' => '',
	),
);
?>