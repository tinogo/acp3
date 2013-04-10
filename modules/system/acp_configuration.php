<?php
/**
 * System
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit;

if (isset($_POST['submit']) === true) {
	if (ACP3_Validate::isInternalURI($_POST['homepage']) === false)
		$errors['homepage'] = ACP3_CMS::$lang->t('system', 'incorrect_homepage');
	if (ACP3_Validate::isNumber($_POST['entries']) === false)
		$errors['entries'] = ACP3_CMS::$lang->t('system', 'select_records_per_page');
	if (ACP3_Validate::isNumber($_POST['flood']) === false)
		$errors['flood'] = ACP3_CMS::$lang->t('system', 'type_in_flood_barrier');
	if ((bool) preg_match('/\/$/', $_POST['icons_path']) === false)
		$errors['icons-path'] = ACP3_CMS::$lang->t('system', 'incorrect_path_to_icons');
	if ($_POST['wysiwyg'] != 'textarea' && (preg_match('=/=', $_POST['wysiwyg']) || is_file(INCLUDES_DIR . 'wysiwyg/' . $_POST['wysiwyg'] . '/info.xml') === false))
		$errors['wysiwyg'] = ACP3_CMS::$lang->t('system', 'select_editor');
	if (empty($_POST['date_format_long']) || empty($_POST['date_format_short']))
		$errors[] = ACP3_CMS::$lang->t('system', 'type_in_date_format');
	if (ACP3_Validate::timeZone($_POST['date_time_zone']) === false)
		$errors['date-time-zone'] = ACP3_CMS::$lang->t('system', 'select_time_zone');
	if (ACP3_Validate::isNumber($_POST['maintenance_mode']) === false)
		$errors[] = ACP3_CMS::$lang->t('system', 'select_online_maintenance');
	if (strlen($_POST['maintenance_message']) < 3)
		$errors['maintenance-message'] = ACP3_CMS::$lang->t('system', 'maintenance_message_to_short');
	if (empty($_POST['seo_title']))
		$errors['seo-title'] = ACP3_CMS::$lang->t('system', 'title_to_short');
	if (ACP3_Validate::isNumber($_POST['seo_robots']) === false)
		$errors[] = ACP3_CMS::$lang->t('system', 'select_seo_robots');
	if (ACP3_Validate::isNumber($_POST['seo_aliases']) === false)
		$errors[] = ACP3_CMS::$lang->t('system', 'select_seo_aliases');
	if (ACP3_Validate::isNumber($_POST['seo_mod_rewrite']) === false)
		$errors[] = ACP3_CMS::$lang->t('system', 'select_mod_rewrite');
	if (ACP3_Validate::isNumber($_POST['cache_images']) === false)
		$errors[] = ACP3_CMS::$lang->t('system', 'select_cache_images');
	if (ACP3_Validate::isNumber($_POST['cache_minify']) === false)
		$errors['cache-minify'] = ACP3_CMS::$lang->t('system', 'type_in_minify_cache_lifetime');
	if (!empty($_POST['extra_css']) && ACP3_Validate::extraCSS($_POST['extra_css']) === false)
		$errors['extra-css'] = ACP3_CMS::$lang->t('system', 'type_in_additional_stylesheets');
	if (!empty($_POST['extra_js']) && ACP3_Validate::extraJS($_POST['extra_js']) === false)
		$errors['extra-js'] = ACP3_CMS::$lang->t('system', 'type_in_additional_javascript_files');
	if ($_POST['mailer_type'] === 'smtp') {
		if (empty($_POST['mailer_smtp_host']))
			$errors['mailer-smtp-host'] = ACP3_CMS::$lang->t('system', 'type_in_mailer_smtp_host');
		if (ACP3_Validate::isNumber($_POST['mailer_smtp_port']) === false)
			$errors['mailer-smtp-port'] = ACP3_CMS::$lang->t('system', 'type_in_mailer_smtp_port');
		if ($_POST['mailer_smtp_auth'] == 1 && empty($_POST['mailer_smtp_user']))
			$errors['mailer-smtp-username'] = ACP3_CMS::$lang->t('system', 'type_in_mailer_smtp_username');
	}

	if (isset($errors) === true) {
		ACP3_CMS::$view->assign('error_msg', errorBox($errors));
	} elseif (ACP3_Validate::formToken() === false) {
		ACP3_CMS::$view->setContent(errorBox(ACP3_CMS::$lang->t('system', 'form_already_submitted')));
	} else {
		// Config aktualisieren
		$config = array(
			'cache_images' => (int) $_POST['cache_images'],
			'cache_minify' => (int) $_POST['cache_minify'],
			'date_format_long' => str_encode($_POST['date_format_long']),
			'date_format_short' => str_encode($_POST['date_format_short']),
			'date_time_zone' => $_POST['date_time_zone'],
			'entries' => (int) $_POST['entries'],
			'extra_css' => $_POST['extra_css'],
			'extra_js' => $_POST['extra_js'],
			'flood' => (int) $_POST['flood'],
			'homepage' => $_POST['homepage'],
			'icons_path' => $_POST['icons_path'],
			'mailer_smtp_auth' => (int) $_POST['mailer_smtp_auth'],
			'mailer_smtp_host' => $_POST['mailer_smtp_host'],
			'mailer_smtp_password' => $_POST['mailer_smtp_password'],
			'mailer_smtp_port' => (int) $_POST['mailer_smtp_port'],
			'mailer_smtp_security' => $_POST['mailer_smtp_security'],
			'mailer_smtp_user' => $_POST['mailer_smtp_user'],
			'mailer_type' => $_POST['mailer_type'],
			'maintenance_message' => $_POST['maintenance_message'],
			'maintenance_mode' => (int) $_POST['maintenance_mode'],
			'seo_aliases' => (int) $_POST['seo_aliases'],
			'seo_meta_description' => str_encode($_POST['seo_meta_description']),
			'seo_meta_keywords' => str_encode($_POST['seo_meta_keywords']),
			'seo_mod_rewrite' => (int) $_POST['seo_mod_rewrite'],
			'seo_robots' => (int) $_POST['seo_robots'],
			'seo_title' => str_encode($_POST['seo_title']),
			'wysiwyg' => $_POST['wysiwyg']
		);

		$bool = ACP3_Config::setSettings('system', $config);

		// Gecachete Stylesheets und JavaScript Dateien löschen
		if (CONFIG_EXTRA_CSS !== $_POST['extra_css'] ||
			CONFIG_EXTRA_JS !== $_POST['extra_js']) {
			ACP3_Cache::purge('minify');
		}

		ACP3_CMS::$session->unsetFormToken();

		setRedirectMessage($bool, ACP3_CMS::$lang->t('system', $bool === true ? 'config_edit_success' : 'config_edit_error'), 'acp/system/configuration');
	}
}
if (isset($_POST['submit']) === false || isset($errors) === true && is_array($errors) === true) {
	getRedirectMessage();

	ACP3_CMS::$view->assign('entries', recordsPerPage(CONFIG_ENTRIES));

	// WYSIWYG-Editoren
	$editors = scandir(INCLUDES_DIR . 'wysiwyg');
	$c_editors = count($editors);
	$wysiwyg = array();

	for ($i = 0; $i < $c_editors; ++$i) {
		$info = ACP3_XML::parseXmlFile(INCLUDES_DIR . 'wysiwyg/' . $editors[$i] . '/info.xml', '/editor');
		if (!empty($info)) {
			$wysiwyg[$i]['value'] = $editors[$i];
			$wysiwyg[$i]['selected'] = selectEntry('wysiwyg', $editors[$i], CONFIG_WYSIWYG);
			$wysiwyg[$i]['lang'] = $info['name'] . ' ' . $info['version'];
		}
	}
	// Normale <textarea>
	$wysiwyg[$i]['value'] = 'textarea';
	$wysiwyg[$i]['selected'] = selectEntry('wysiwyg', 'textarea', CONFIG_WYSIWYG);
	$wysiwyg[$i]['lang'] = ACP3_CMS::$lang->t('system', 'textarea');
	ACP3_CMS::$view->assign('wysiwyg', $wysiwyg);

	// Zeitzonen
	ACP3_CMS::$view->assign('time_zones', ACP3_CMS::$date->getTimeZones(CONFIG_DATE_TIME_ZONE));

	// Wartungsmodus an/aus
	$maintenance = array();
	$maintenance[0]['value'] = '1';
	$maintenance[0]['checked'] = selectEntry('maintenance_mode', '1', CONFIG_MAINTENANCE_MODE, 'checked');
	$maintenance[0]['lang'] = ACP3_CMS::$lang->t('system', 'yes');
	$maintenance[1]['value'] = '0';
	$maintenance[1]['checked'] = selectEntry('maintenance_mode', '0', CONFIG_MAINTENANCE_MODE, 'checked');
	$maintenance[1]['lang'] = ACP3_CMS::$lang->t('system', 'no');
	ACP3_CMS::$view->assign('maintenance', $maintenance);

	// Robots
	$robots = array();
	$robots[0]['value'] = '1';
	$robots[0]['selected'] = selectEntry('seo_robots', '1', CONFIG_SEO_ROBOTS);
	$robots[0]['lang'] = ACP3_CMS::$lang->t('system', 'seo_robots_index_follow');
	$robots[1]['value'] = '2';
	$robots[1]['selected'] = selectEntry('seo_robots', '2', CONFIG_SEO_ROBOTS);
	$robots[1]['lang'] = ACP3_CMS::$lang->t('system', 'seo_robots_index_nofollow');
	$robots[2]['value'] = '3';
	$robots[2]['selected'] = selectEntry('seo_robots', '3', CONFIG_SEO_ROBOTS);
	$robots[2]['lang'] = ACP3_CMS::$lang->t('system', 'seo_robots_noindex_follow');
	$robots[3]['value'] = '4';
	$robots[3]['selected'] = selectEntry('seo_robots', '4', CONFIG_SEO_ROBOTS);
	$robots[3]['lang'] = ACP3_CMS::$lang->t('system', 'seo_robots_noindex_nofollow');
	ACP3_CMS::$view->assign('robots', $robots);

	// URI-Aliases aktivieren/deaktivieren
	$aliases = array();
	$aliases[0]['value'] = '1';
	$aliases[0]['checked'] = selectEntry('seo_aliases', '1', CONFIG_SEO_ALIASES, 'checked');
	$aliases[0]['lang'] = ACP3_CMS::$lang->t('system', 'yes');
	$aliases[1]['value'] = '0';
	$aliases[1]['checked'] = selectEntry('seo_aliases', '0', CONFIG_SEO_ALIASES, 'checked');
	$aliases[1]['lang'] = ACP3_CMS::$lang->t('system', 'no');
	ACP3_CMS::$view->assign('aliases', $aliases);

	// Sef-URIs
	$mod_rewrite = array();
	$mod_rewrite[0]['value'] = '1';
	$mod_rewrite[0]['checked'] = selectEntry('seo_mod_rewrite', '1', CONFIG_SEO_MOD_REWRITE, 'checked');
	$mod_rewrite[0]['lang'] = ACP3_CMS::$lang->t('system', 'yes');
	$mod_rewrite[1]['value'] = '0';
	$mod_rewrite[1]['checked'] = selectEntry('seo_mod_rewrite', '0', CONFIG_SEO_MOD_REWRITE, 'checked');
	$mod_rewrite[1]['lang'] = ACP3_CMS::$lang->t('system', 'no');
	ACP3_CMS::$view->assign('mod_rewrite', $mod_rewrite);

	// Caching von Bildern
	$cache_images = array();
	$cache_images[0]['value'] = '1';
	$cache_images[0]['checked'] = selectEntry('cache_images', '1', CONFIG_CACHE_IMAGES, 'checked');
	$cache_images[0]['lang'] = ACP3_CMS::$lang->t('system', 'yes');
	$cache_images[1]['value'] = '0';
	$cache_images[1]['checked'] = selectEntry('cache_images', '0', CONFIG_CACHE_IMAGES, 'checked');
	$cache_images[1]['lang'] = ACP3_CMS::$lang->t('system', 'no');
	ACP3_CMS::$view->assign('cache_images', $cache_images);

	// Mailertyp
	$mailer_type = array();
	$mailer_type[0]['value'] = 'mail';
	$mailer_type[0]['selected'] = selectEntry('mailer_type', 'mail', CONFIG_MAILER_TYPE);
	$mailer_type[0]['lang'] = ACP3_CMS::$lang->t('system', 'mailer_type_php_mail');
	$mailer_type[1]['value'] = 'smtp';
	$mailer_type[1]['selected'] = selectEntry('mailer_type', 'smtp', CONFIG_MAILER_TYPE);
	$mailer_type[1]['lang'] = ACP3_CMS::$lang->t('system', 'mailer_type_smtp');
	ACP3_CMS::$view->assign('mailer_type', $mailer_type);

	// Mailer SMTP Authentifizierung
	$mailer_smtp_auth = array();
	$mailer_smtp_auth[0]['value'] = '1';
	$mailer_smtp_auth[0]['checked'] = selectEntry('seo_aliases', '1', CONFIG_MAILER_SMTP_AUTH, 'checked');
	$mailer_smtp_auth[0]['lang'] = ACP3_CMS::$lang->t('system', 'yes');
	$mailer_smtp_auth[1]['value'] = '0';
	$mailer_smtp_auth[1]['checked'] = selectEntry('seo_aliases', '0', CONFIG_MAILER_SMTP_AUTH, 'checked');
	$mailer_smtp_auth[1]['lang'] = ACP3_CMS::$lang->t('system', 'no');
	ACP3_CMS::$view->assign('mailer_smtp_auth', $mailer_smtp_auth);

	// Mailer SMTP Verschlüsselung
	$mailer_smtp_security = array();
	$mailer_smtp_security[0]['value'] = 'none';
	$mailer_smtp_security[0]['selected'] = selectEntry('mailer_smtp_security', '', CONFIG_MAILER_SMTP_SECURITY);
	$mailer_smtp_security[0]['lang'] = ACP3_CMS::$lang->t('system', 'mailer_smtp_security_none');
	$mailer_smtp_security[1]['value'] = 'ssl';
	$mailer_smtp_security[1]['selected'] = selectEntry('mailer_smtp_security', 'ssl', CONFIG_MAILER_SMTP_SECURITY);
	$mailer_smtp_security[1]['lang'] = ACP3_CMS::$lang->t('system', 'mailer_smtp_security_ssl');
	$mailer_smtp_security[2]['value'] = 'tls';
	$mailer_smtp_security[2]['selected'] = selectEntry('mailer_smtp_security', 'tls', CONFIG_MAILER_SMTP_SECURITY);
	$mailer_smtp_security[2]['lang'] = ACP3_CMS::$lang->t('system', 'mailer_smtp_security_tls');
	ACP3_CMS::$view->assign('mailer_smtp_security', $mailer_smtp_security);

	$settings = ACP3_Config::getSettings('system');

	ACP3_CMS::$view->assign('form', isset($_POST['submit']) ? $_POST : $settings);

	ACP3_CMS::$session->generateFormToken();
}