<?php
if (!defined('IN_INSTALL'))
	exit;

if (isset($_POST['submit'])) {
	$form = $_POST['form'];
	$config_path = ACP3_ROOT . 'includes/config.php';
	
	if (empty($form['db_host']))
		$errors[] = lang('system', 'type_in_db_host');
	if (empty($form['db_user']))
		$errors[] = lang('system', 'type_in_db_username');
	if (empty($form['db_name']))
		$errors[] = lang('system', 'type_in_db_name');
	if (empty($form['db_type']))
		$errors[] = lang('system', 'select_db_type');
	if (!empty($form['db_host']) && !empty($form['db_user']) && !empty($form['db_name'])) {
		if ($form['db_type'] == 'mysql') {
			$db = @mysql_connect($form['db_host'], $form['db_user'], $form['db_pwd']);
			$db_select = @mysql_select_db($form['db_name'], $db);
			if (!$db || !$db_select)
				$errors[] = lang('installation', 'db_connection_failed');
		} elseif ($form['db_type'] == 'mysqli') {
			$db = @mysqli_connect($form['db_host'], $form['db_user'], $form['db_pwd'], $form['db_name']);
			if (mysqli_connect_errno())
				$errors[] = lang('installation', 'db_connection_failed');
		}
	}
	if (empty($form['user_name']))
		$errors[] = lang('installation', 'type_in_user_name');
	if ((empty($form['user_pwd']) || empty($form['user_pwd_wdh'])) || (!empty($form['user_pwd']) && !empty($form['user_pwd_wdh']) && $form['user_pwd'] != $form['user_pwd_wdh']))
		$errors[] = lang('installation', 'type_in_pwd');
	if (!validate::email($form['mail']))
		$errors[] = lang('common', 'wrong_email_format');
	if (!validate::isNumber($form['entries']))
		$errors[] = lang('system', 'select_entries_per_page');
	if (!validate::isNumber($form['flood']))
		$errors[] = lang('system', 'type_in_flood_barrier');
	if (empty($form['date']))
		$errors[] = lang('system', 'type_in_date_format');
	if (!validate::isNumber($form['dst']))
		$errors[] = lang('common', 'select_daylight_saving_time');
	if (!validate::isNumber($form['time_zone']))
		$errors[] = lang('common', 'select_time_zone');
	if (!is_file($config_path) || !is_writable($config_path))
		$errors[] = lang('installation', 'wrong_chmod_for_config_file');

	if (isset($errors)) {
		$tpl->assign('errors', $errors);
		$tpl->assign('error_msg', $tpl->fetch('error.html'));
	} else {
		// Modulkonfigurationsdateien schreiben
		write_config('contact', array('mail' => $form['mail'], 'address' => '', 'telephone' => '', 'fax' => '', 'disclaimer' => lang('installation', 'disclaimer'), 'miscellaneous' => ''));
		write_config('newsletter', array('mail' => $form['mail'], 'mailsig' => lang('installation', 'sincerely') . "\n\n" . lang('installation', 'newsletter_mailsig')));

		// Systemkonfiguration erstellen
		$config = array(
			'date' => mask($form['date']),
			'db_host' => $form['db_host'],
			'db_name' => $form['db_name'],
			'db_pre' => mask($form['db_pre']),
			'db_pwd' => $form['db_pwd'],
			'db_type' => mask($form['db_type']),
			'db_user' => $form['db_user'],
			'design' => 'acp3',
			'dst' => $form['dst'],
			'entries' => $form['entries'],
			'flood' => $form['flood'],
			'homepage' => 'news/list/',
			'lang' => LANG,
			'maintenance' => 0,
			'maintenance_msg' => lang('installation', 'offline_message'),
			'meta_description' => '',
			'meta_keywords' => '',
			'sef' => 0,
			'time_zone' => $form['time_zone'],
			'title' => !empty($form['title']) ? mask($form['title']) : 'ACP3',
			'version' => CONFIG_VERSION
		);

		$pattern = "define('CONFIG_%s', '%s');\n";
		$config_file = "<?php\n";
		$config_file.= "define('INSTALLED', true);\n";
		foreach ($config as $key => $value) {
			$config_file.= sprintf($pattern, strtoupper($key), $value);
		}
		$config_file.= '?>';

		// Daten in die config.php schreiben und diese laden
		@file_put_contents($config_path, $config_file);
		require $config_path;

		$db = new db();

		$sql_file = file_get_contents(ACP3_ROOT . 'installation/modules/install/install.sql');
		$sql_file = str_replace(array("\r\n", "\r"), "\n", $sql_file);
		$sql_file = str_replace('{pre}', CONFIG_DB_PRE, $sql_file);
		if (version_compare(mysql_get_client_info(), '4.1', '>=')) {
			$sql_file = str_replace('{engine}', 'ENGINE=MyISAM CHARACTER SET `utf8` COLLATE `utf8_general_ci`', $sql_file);
		} else {
			$sql_file = str_replace('{engine}', 'TYPE=MyISAM CHARSET=utf-8', $sql_file);
		}

		$sql_file_arr = explode(";\n", $sql_file);
		$salt = salt(12);
		$current_date = gmdate('U');

		$other_arr = array(
			1 => 'INSERT INTO `' . CONFIG_DB_PRE . 'users` VALUES (1, \'' . mask($form['user_name']) . '\', \'\', \'' . sha1($salt . sha1($form['user_pwd'])) . ':' . $salt . '\', 1, \'' . $form['mail'] . '\', \'\', \'' . CONFIG_TIME_ZONE . '\', \'' . CONFIG_DST .'\', \'' . CONFIG_LANG . '\', \'\')',
			2 => 'INSERT INTO `' . CONFIG_DB_PRE . 'news` VALUES (\'\', \'' . $current_date . '\', \'' . $current_date . '\', \'' . lang('installation', 'news_headline') . '\', \'' . lang('installation', 'news_text') . '\', \'1\', \'\', \'\', \'\')',
			3 => 'INSERT INTO `' . CONFIG_DB_PRE . 'pages` VALUES (\'\', \'' . $current_date . '\', \'' . $current_date . '\', 2, 0, 1, 0, \'' . lang('installation', 'pages_news') . '\', \'news/list\', 1, \'\'), (\'\', \'' . $current_date . '\', \'' . $current_date . '\', 2, 0, 1, 1, \'' . lang('installation', 'pages_files') . '\', \'files/list\', 1, \'\'), (\'\', \'' . $current_date . '\', \'' . $current_date . '\', 2, 0, 1, 2, \'' . lang('installation', 'pages_gallery') . '\', \'gallery/list\', 1, \'\'), (\'\', \'' . $current_date . '\', \'' . $current_date . '\', 2, 0, 1, 3, \'' . lang('installation', 'pages_guestbook') . '\', \'guestbook/list\', 1, \'\'), (\'\', \'' . $current_date . '\', \'' . $current_date . '\', 2, 0, 1, 4, \'' . lang('installation', 'pages_polls') . '\', \'polls/list\', 1, \'\'), (\'\', \'' . $current_date . '\', \'' . $current_date . '\', 2, 0, 1, 5, \'' . lang('installation', 'pages_search') . '\', \'search/list\', 1, \'\'), (\'\', \'' . $current_date . '\', \'' . $current_date . '\', 2, 0, 2, 0, \'' . lang('installation', 'pages_contact') . '\', \'contact/list\', 1, \'\'), (\'\', \'' . $current_date . '\', \'' . $current_date . '\', 2, 0, 2, 1, \'' . lang('installation', 'pages_imprint') . '\', \'contact/imprint\', 1, \'\')',
			4 => 'INSERT INTO `' . CONFIG_DB_PRE . 'pages_blocks` (`id`, `index_name`, `title`) VALUES (1, \'main\', \'' . lang('installation', 'pages_main') . '\'), (2, \'sidebar\', \'' . lang('installation', 'pages_sidebar') . '\')',
		);
		$queries = array_merge($sql_file_arr, $other_arr);

		$data = NULL;
		$i = 0;
		foreach ($queries as $query) {
			if (!empty($query)) {
				$query.= ';';
				$data[$i]['query'] = $query;
				$bool = $db->query($query, 3);
				$data[$i]['color'] = $bool == true ? '090' : 'f00';
				$data[$i]['result'] = $bool == true ? lang('installation', 'query_successfully_executed') : lang('installation', 'query_failed');
				$i++;
				if ($bool != true) {
					$tpl->assign('install_error', true);
					break;
				}
			}
		}
		$tpl->assign('sql_queries', $data);

		// Bei einer erneuten Installation den alten Cache löschen
		cache::purge();
	}
}
if (!isset($_POST['submit']) || isset($errors) && is_array($errors)) {
	// Einträge pro Seite
	$i = 0;
	for ($j = 10; $j <= 50; $j = $j + 10) {
		$entries[$i]['value'] = $j;
		$entries[$i]['selected'] = select_entry('entries', $j, '20');
		$i++;
	}
	$tpl->assign('entries', $entries);

	// Sef-URIs
	$sef[0]['checked'] = select_entry('sef', '1', '0', 'checked');
	$sef[1]['checked'] = select_entry('sef', '0', '0', 'checked');
	$tpl->assign('sef', $sef);

	// Zeitzonen
	$time_zones = array(-12, -11, -10, -9.5, -9, -8, -7, -6, -5, -4, -3.5, -3, -2, -1, 0, 1, 2, 3, 3.5, 4, 4.5, 5, 5.5, 5.75, 6, 6.5, 7, 8, 8.75, 9, 9.5, 10, 10.5, 11, 11.5, 12, 12.75, 13, 14);
	$check_dst = date('I');
	$offset = date('Z') - ($check_dst == '1' ? 3600 : 0);
	$i = 0;
	foreach ($time_zones as $row) {
		$time_zone[$i]['value'] = $row * 3600;
		$time_zone[$i]['selected'] = select_entry('time_zone', $row * 3600, $offset);
		$time_zone[$i]['lang'] = lang('common', 'utc' . $row);
		$i++;
	}
	$tpl->assign('time_zone', $time_zone);

	// Sommerzeit an/aus
	$dst[0]['checked'] = select_entry('dst', '1', $check_dst, 'checked');
	$dst[1]['checked'] = select_entry('dst', '0', $check_dst, 'checked');
	$tpl->assign('dst', $dst);

	$defaults['db_pre'] = 'acp3_';
	$defaults['user_name'] = 'admin';
	$defaults['flood'] = '30';
	$defaults['date'] = 'd.m.y, H:i';
	$defaults['title'] = 'ACP3';
	
	$tpl->assign('form', isset($form) ? $form : $defaults);

	$default_db_type = extension_loaded('mysqli') ? 'mysqli' : 'mysql';

	$db_type[0]['value'] = 'mysql';
	$db_type[0]['selected'] = select_entry('db_type', 'mysql', $default_db_type);
	$db_type[0]['lang'] = 'MySQL';
	if (extension_loaded('mysqli'))	{
		$db_type[1]['value'] = 'mysqli';
		$db_type[1]['selected'] = select_entry('db_type', 'mysqli', $default_db_type);
		$db_type[1]['lang'] = 'MySQLi';
	}
	$tpl->assign('db_type', $db_type);
}
$content = $tpl->fetch('configuration.html');
?>