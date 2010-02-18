<?php
/**
 * Files
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_ADM'))
	exit;

require_once ACP3_ROOT . 'modules/categories/functions.php';

$settings = config::output('files');

if (isset($_POST['form'])) {
	$form = $_POST['form'];
	if (isset($form['external'])) {
		$file = $form['file_external'];
	} else {
		$file['tmp_name'] = $_FILES['file_internal']['tmp_name'];
		$file['name'] = $_FILES['file_internal']['name'];
		$file['size'] = $_FILES['file_internal']['size'];
	}

	if (!validate::date($form['start'], $form['end']))
		$errors[] = $lang->t('common', 'select_date');
	if (strlen($form['link_title']) < 3)
		$errors[] = $lang->t('files', 'type_in_link_title');
	if (isset($form['external']) && (empty($file) || empty($form['filesize']) || empty($form['unit'])))
		$errors[] = $lang->t('files', 'type_in_external_resource');
	if (!isset($form['external']) && (empty($file['tmp_name']) || empty($file['size'])))
		$errors[] = $lang->t('files', 'select_internal_resource');
	if (strlen($form['text']) < 3)
		$errors[] = $lang->t('files', 'description_to_short');
	if (strlen($form['cat_create']) < 3 && !categoriesCheck($form['cat']))
		$errors[] = $lang->t('files', 'select_category');
	if (strlen($form['cat_create']) >= 3 && categoriesCheckDuplicate($form['cat_create'], 'files'))
		$errors[] = $lang->t('categories', 'category_already_exists');

	if (isset($errors)) {
		$tpl->assign('error_msg', comboBox($errors));
	} else {
		if (is_array($file)) {
			$result = moveFile($file['tmp_name'], $file['name'], 'files');
			$new_file = $result['name'];
			$filesize = $result['size'];
		} else {
			$form['filesize'] = (float) $form['filesize'];
			$new_file = $file;
			$filesize = $form['filesize'] . ' ' . db::escape($form['unit']);
		}

		$insert_values = array(
			'id' => '',
			'start' => $date->timestamp($form['start']),
			'end' => $date->timestamp($form['end']),
			'category_id' => strlen($form['cat_create']) >= 3 ? categoriesCreate($form['cat_create'], 'files') : $form['cat'],
			'file' => $new_file,
			'size' => $filesize,
			'link_title' => db::escape($form['link_title']),
			'text' => db::escape($form['text'], 2),
			'comments' => $settings['comments'] == 1 && isset($form['comments']) ? 1 : 0,
		);

		require_once ACP3_ROOT . 'modules/files/functions.php';

		$bool = $db->insert('files', $insert_values);
		setFilesCache($db->link->lastInsertId());

		$content = comboBox($bool ? $lang->t('common', 'create_success') : $lang->t('common', 'create_error'), uri('acp/files'));
	}
}
if (!isset($_POST['form']) || isset($errors) && is_array($errors)) {
	// Datumsauswahl
	$tpl->assign('publication_period', datepicker(array('start', 'end')));

	$units[0]['value'] = 'Byte';
	$units[0]['selected'] = selectEntry('unit', 'Byte');
	$units[1]['value'] = 'KiB';
	$units[1]['selected'] = selectEntry('unit', 'KiB');
	$units[2]['value'] = 'MiB';
	$units[2]['selected'] = selectEntry('unit', 'MiB');
	$units[3]['value'] = 'GiB';
	$units[3]['selected'] = selectEntry('unit', 'GiB');
	$units[4]['value'] = 'TiB';
	$units[4]['selected'] = selectEntry('unit', 'TiB');
	$tpl->assign('units', $units);

	// Formularelemente
	$tpl->assign('categories', categoriesList('files', '', true));

	if (modules::check('comments', 'functions') == 1 && $settings['comments'] == 1) {
		$options = array();
		$options[0]['name'] = 'comments';
		$options[0]['checked'] = selectEntry('comments', '1', '0', 'checked');
		$options[0]['lang'] = $lang->t('common', 'allow_comments');
		$tpl->assign('options', $options);
	}

	$tpl->assign('checked_external', isset($form['external']) ? ' checked="checked"' : '');

	$defaults = array(
		'link_title' => '',
		'file_internal' => '',
		'file_external' => '',
		'filesize' => '',
		'text' => '',
	);

	$tpl->assign('form', isset($form) ? $form : $defaults);

	$content = $tpl->fetch('files/create.html');
}
