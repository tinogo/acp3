<?php
/**
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Core
 */

// Standardzeitzone festlegen
date_default_timezone_set('UTC');

// register_globals OFF Emulation
require_once ACP3_ROOT . 'includes/globals.php';

// Konfiguration des ACP3 laden
require_once ACP3_ROOT . 'includes/config.php';
if (!defined('INSTALLED')) {
	exit('Das ACP3 ist nicht richtig installiert. Bitte führen Sie den <a href="' . ACP3_ROOT . 'installation/">Installationsassistenten</a> aus und folgen Sie den Anweisungen.');
}

// Wenn der DEBUG Modus aktiv ist, Fehler ausgeben
$reporting_level = defined('DEBUG') && DEBUG ? E_ALL | E_STRICT : 0;
error_reporting($reporting_level);

function __autoload($className)
{
	require_once ACP3_ROOT . 'includes/classes/' . $className . '.php';
}

// Einige Konstanten definieren
define('PHP_SELF', htmlentities($_SERVER['SCRIPT_NAME']));
$php_self = dirname(PHP_SELF);
define('ROOT_DIR', $php_self != '/' ? $php_self . '/' : '/');
define('DESIGN_PATH', ROOT_DIR . 'designs/' . CONFIG_DESIGN . '/');

// Smarty einbinden
define('SMARTY_DIR', ACP3_ROOT . 'includes/smarty/');
require SMARTY_DIR . 'Smarty.class.php';
$tpl = new smarty();
$tpl->template_dir = ACP3_ROOT . 'designs/' . CONFIG_DESIGN . '/';
$tpl->compile_dir = ACP3_ROOT . 'cache/';
$tpl->error_reporting = $reporting_level;
if (!defined('DEBUG') || !DEBUG)
	$tpl->compile_check = false;
if (!is_writable($tpl->compile_dir)) {
	exit('Bitte geben Sie dem "cache"-Ordner den CHMOD 777!');
}

// Einige Template Variablen setzen
$tpl->assign('PHP_SELF', PHP_SELF);
$tpl->assign('REQUEST_URI', htmlentities($_SERVER['REQUEST_URI']));
$tpl->assign('ROOT_DIR', ROOT_DIR);
$tpl->assign('DESIGN_PATH', DESIGN_PATH);
$tpl->assign('LANG', CONFIG_LANG);
$tpl->assign('PAGE_TITLE', CONFIG_SEO_TITLE);
$tpl->assign('KEYWORDS', CONFIG_SEO_META_KEYWORDS);
$tpl->assign('DESCRIPTION', CONFIG_SEO_META_DESCRIPTION);

$uri = new uri();

// Falls der Wartungsmodus aktiv ist, Wartungsnachricht ausgeben und Skript beenden
if (CONFIG_MAINTENANCE_MODE == '1' && defined('IN_ACP3')) {
	header('Content-Type: text/html; charset=UTF-8');
	$tpl->assign('maintenance_msg', CONFIG_MAINTENANCE_MESSAGE);
	$tpl->display('maintenance.html');
	exit;
}

// Klassen initialisieren
$db = new db();
$handle = $db->connect(CONFIG_DB_HOST, CONFIG_DB_NAME, CONFIG_DB_USER, CONFIG_DB_PASSWORD, CONFIG_DB_PRE);
if ($handle !== true) {
	exit($handle);
}
$tpl->assign('MODULES', new modules());

require_once ACP3_ROOT . 'includes/functions.php';
$auth = new auth();
$lang = new lang();
$date = new date();