<?php
/**
 * Gallery
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_ADM'))
	exit;

if (!empty($modules->id) && $db->select('id', 'galpics', 'id = \'' . $modules->id . '\'', 0, 0, 0, 1) == '1') {
	$picture = $db->select('p.pic, p.gallery_id, p.file, p.description, g.name AS gallery_name', 'galpics AS p, ' . CONFIG_DB_PRE . 'gallery AS g', 'p.id = \'' . $modules->id . '\' AND p.gallery_id = g.id');

	$breadcrumb->assign(lang('common', 'acp'), uri('acp'));
	$breadcrumb->assign(lang('gallery', 'gallery'), uri('acp/gallery'));
	$breadcrumb->assign($picture[0]['gallery_name'], uri('acp/gallery/edit_gallery/id_' . $picture[0]['gallery_id']));
	$breadcrumb->assign(lang('gallery', 'edit_picture'));

	if (isset($_POST['submit'])) {
		if (!empty($_FILES['file']['tmp_name']) && $_FILES['file']['size'] > '0') {
			$file['tmp_name'] = $_FILES['file']['tmp_name'];
			$file['name'] = $_FILES['file']['name'];
			$file['size'] = $_FILES['file']['size'];
		}
		$form = $_POST['form'];
		$settings = $config->output('gallery');

		if (!$validate->isNumber($form['gallery']) || $db->select('id', 'gallery', 'id = \'' . $form['gallery'] . '\'', 0, 0, 0, 1) != '1')
			$errors[] = lang('gallery', 'no_gallery_selected');
		if (!$validate->isNumber($form['pic']))
			$errors[] = lang('gallery', 'type_in_picture_number');
		if (isset($file) && is_array($file) && !$validate->isPicture($file['tmp_name'], $settings['width'], $settings['height'], $settings['filesize']))
			$errors[] = lang('gallery', 'invalid_image_selected');

		if (isset($errors)) {
			$tpl->assign('error_msg', comboBox($errors));
		} else {
			$new_file_sql = null;
			if (isset($file) && is_array($file)) {
				$result = moveFile($file['tmp_name'], $file['name'], 'gallery');
				$new_file_sql['file'] = $result['name'];
			}

			$update_values = array(
				'pic' => $form['pic'],
				'gallery_id' => $form['gallery'],
				'description' => $db->escape($form['description'], 2),
			);
			if (is_array($new_file_sql)) {
				$old_file = $db->select('file', 'galpics', 'id = \'' . $modules->id . '\'');
				removeFile('gallery', $old_file[0]['file']);

				$update_values = array_merge($update_values, $new_file_sql);
			}

			$bool = $db->update('galpics', $update_values, 'id = \'' . $modules->id . '\'');

			$cache->create('gallery_pics_id_' . $form['gallery'], $db->select('id', 'galpics', 'gallery_id = \'' . $modules->id . '\'', 'id ASC'));

			$content = comboBox($bool ? lang('gallery', 'edit_picture_success') : lang('gallery', 'edit_picture_error'), uri('acp/gallery'));
		}
	}
	if (!isset($_POST['submit']) || isset($errors) && is_array($errors)) {
		$picture[0]['description'] = $db->escape($picture[0]['description'], 3);

		$galleries = $db->select('id, start, name', 'gallery', 0, 'start DESC');
		$c_galleries = count($galleries);

		for ($i = 0; $i < $c_galleries; $i++) {
			$galleries[$i]['selected'] = selectEntry('gallery', $galleries[$i]['id'], $picture[0]['gallery_id']);
			$galleries[$i]['date'] = dateAligned(1, $galleries[$i]['start']);
			$galleries[$i]['name'] = $db->escape($galleries[$i]['name'], 3);
		}
		$tpl->assign('galleries', $galleries);

		$tpl->assign('form', isset($form) ? $form : $picture[0]);

		$content = $tpl->fetch('gallery/edit_picture.html');
	}
} else {
	redirect('errors/404');
}
?>