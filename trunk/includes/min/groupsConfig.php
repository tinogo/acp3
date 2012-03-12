<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/** 
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 *
 * See http://code.google.com/p/minify/wiki/CustomSource for other ideas
 **/

define('ACP3_ROOT', realpath(dirname(__FILE__) . '/../../') . '/');

require_once ACP3_ROOT . 'includes/config.php';

define('DESIGN_PATH', ACP3_ROOT . 'designs/' . CONFIG_DESIGN . '/');

if ($_GET['g'] === 'css') {
	define('IN_ACP3', true);
	define('PHP_SELF', htmlentities($_SERVER['SCRIPT_NAME']));
	$php_self = dirname(PHP_SELF);
	define('ROOT_DIR', $php_self != '/' ? $php_self . '/' : '/');
	define('MODULES_DIR', ACP3_ROOT . 'modules/');
        define('INCLUDES_DIR', ACP3_ROOT . 'includes/');

        /**
        * Autoloading für die ACP3 eigenen Klassen
        *
        * @param string $class
        *  Der Name der zu ladenden Klasse
        */
        function acp3_load_class($class)
        {
                $file = INCLUDES_DIR . 'classes/' . str_replace('ACP3_', '', $class) . '.class.php';
                if(is_file($file) === true)
                        require_once $file;
        }
        spl_autoload_register("acp3_load_class");

	// Klassen initialisieren
	$db = new ACP3_DB();
	$handle = $db->connect(CONFIG_DB_HOST, CONFIG_DB_NAME, CONFIG_DB_USER, CONFIG_DB_PASSWORD, CONFIG_DB_PRE);
	if ($handle !== true) {
		exit($handle);
	}

	$session = new ACP3_Session();
	$auth = new ACP3_Auth();
	$lang = new ACP3_Lang();

	$layout = isset($_GET['layout']) && !preg_match('=/=', $_GET['layout']) && is_file(DESIGN_PATH . $_GET['layout'] . '.css') === true ? $_GET['layout']: 'layout';

	$styles = array();
	$styles['css'][] = DESIGN_PATH . $layout . '.css';

	$modules = scandir(DESIGN_PATH);
	foreach ($modules as $module) {
		$path = DESIGN_PATH . $module . '/style.css';
		if ($module !== '.' && $module !== '..' && is_file($path) === true && ACP3_Modules::isActive($module) === true)
			$styles['css'][] = $path;
	}

	$styles['css'][] = DESIGN_PATH . 'jquery/jquery-ui.css';
	$styles['css'][] = DESIGN_PATH . 'jquery/jquery-fancybox.css';

	return $styles;
} elseif ($_GET['g'] === 'js') {
	$scripts = array();
	$scripts['js'][] = DESIGN_PATH . 'jquery/jquery.min.js';
	$scripts['js'][] = DESIGN_PATH . 'jquery/jquery.cookie.js';
	$scripts['js'][] = DESIGN_PATH . 'jquery/jquery.ui.min.js';
	$scripts['js'][] = DESIGN_PATH . 'jquery/jquery.fancybox.js';
	$scripts['js'][] = DESIGN_PATH . 'script.js';

	return $scripts;
}