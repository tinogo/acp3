<?php
if (!defined('IN_INSTALL'))
	exit;

define('PHP_VER', '5.2.0');
define('MYSQL_VER', '4.1');
define('COLOR_ERROR', 'f00');
define('COLOR_SUCCESS', '090');

// Allgemeine Voraussetzungen
$requirements[0]['name'] = lang('system', 'php_version');
$requirements[0]['color'] = version_compare(phpversion(), PHP_VER, '>=') ? COLOR_SUCCESS : COLOR_ERROR;
$requirements[0]['found'] = phpversion();
$requirements[0]['required'] = PHP_VER;
$requirements[1]['name'] = lang('system', 'mysql_version');
$requirements[1]['color'] = version_compare(mysql_get_client_info(), MYSQL_VER, '>=') ? COLOR_SUCCESS : COLOR_ERROR;
$requirements[1]['found'] = mysql_get_client_info();
$requirements[1]['required'] = MYSQL_VER;
$requirements[2]['name'] = lang('installation', 'pdo_extension');
$requirements[2]['color'] = extension_loaded('pdo') && extension_loaded('pdo_mysql') ? COLOR_SUCCESS : COLOR_ERROR;
$requirements[2]['found'] = lang('system', $requirements[2]['color'] == COLOR_ERROR ? 'on' : 'off');
$requirements[2]['required'] = lang('system', 'on');
$requirements[3]['name'] = lang('installation', 'gd_library');
$requirements[3]['color'] = extension_loaded('gd') ? COLOR_SUCCESS : COLOR_ERROR;
$requirements[3]['found'] = lang('system', $requirements[3]['color'] == COLOR_ERROR ? 'on' : 'off');
$requirements[3]['required'] = lang('system', 'on');

$tpl->assign('requirements', $requirements);

$defaults = array(
	'includes/config.php',
	'cache/',
);
// Module
$modules = scandir('../modules/');
foreach ($modules as $row) {
	$path = 'modules/' . $row . '/module.xml';
	if (is_file('../' . $path)) {
		$defaults[] = $path;
	}
}
// Uploadordner
$uploads = scandir('../uploads/');
foreach ($uploads as $row) {
	$path = 'uploads/' . $row . '/';
	if ($row != '.' && $row != '..' && $row != '.svn' &&  is_dir('../' . $path)) {
		$defaults[] = $path;
	}
}
$files_dirs = array();
$check_again = false;

$i = 0;
foreach ($defaults as $row) {
	$files_dirs[$i]['path'] = $row;
	// Überprüfen, ob es eine Datei oder ein Ordner ist
	if (is_file(ACP3_ROOT . $row)) {
		$files_dirs[$i]['color_1'] = COLOR_SUCCESS;
		$files_dirs[$i]['exists'] = lang('installation', 'file_found');
	} elseif (is_dir(ACP3_ROOT . $row)) {
		$files_dirs[$i]['color_1'] = COLOR_SUCCESS;
		$files_dirs[$i]['exists'] = lang('installation', 'folder_found');
	} else {
		$files_dirs[$i]['color_1'] = COLOR_ERROR;
		$files_dirs[$i]['exists'] = lang('installation', 'file_folder_not_found');
	}
	$files_dirs[$i]['color_2'] = is_writable(ACP3_ROOT . $row) ? COLOR_SUCCESS : COLOR_ERROR;
	$files_dirs[$i]['writeable'] = $files_dirs[$i]['color_2'] == COLOR_SUCCESS ? lang('installation', 'writeable') : lang('installation', 'not_writeable');
	if ($files_dirs[$i]['color_1'] == COLOR_ERROR || $files_dirs[$i]['color_2'] == COLOR_ERROR) {
		$check_again = true;
	}
	$i++;
}
$tpl->assign('files_dirs', $files_dirs);

// PHP Einstellungen
$php_settings[0]['setting'] = lang('installation', 'error_messages');
$php_settings[0]['color'] = (bool)ini_get('display_errors') ? COLOR_ERROR : COLOR_SUCCESS;
$php_settings[0]['value'] = lang('system', (bool)ini_get('display_errors') ? 'on' : 'off');
$php_settings[1]['setting'] = lang('installation', 'register_globals');
$php_settings[1]['color'] = (bool)ini_get('register_globals') ? COLOR_ERROR : COLOR_SUCCESS;
$php_settings[1]['value'] = lang('system', (bool)ini_get('register_globals') ? 'on' : 'off');
$php_settings[2]['setting'] = lang('installation', 'maximum_uploadsize');
$php_settings[2]['color'] = ini_get('post_max_size') > 0 ? COLOR_SUCCESS : COLOR_ERROR;
$php_settings[2]['value'] = ini_get('post_max_size');
$php_settings[3]['setting'] = lang('system', 'safe_mode');
$php_settings[3]['color'] =  (bool)ini_get('safe_mode') ? COLOR_ERROR : COLOR_SUCCESS;
$php_settings[3]['value'] = lang('system', (bool)ini_get('safe_mode') ? 'on' : 'off');
$tpl->assign('php_settings', $php_settings);

if (version_compare(phpversion(), PHP_VER, '<') || version_compare(mysql_get_client_info(), MYSQL_VER, '<') || $requirements[2]['color'] == COLOR_ERROR || $requirements[3]['color'] == COLOR_ERROR) {
	$tpl->assign('stop_install', true);
} elseif ($check_again) {
	$tpl->assign('check_again', true);
}

$content = $tpl->fetch('requirements.html');
?>