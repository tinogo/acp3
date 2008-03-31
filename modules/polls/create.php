<?php
/**
 * Polls
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Modules
 */

if (!defined('IN_ADM'))
	exit;

if (isset($_POST['submit'])) {
	$form = $_POST['form'];

	if (!$validate->date($form))
		$errors[] = lang('common', 'select_date');
	if (empty($form['question']))
		$errors[] = lang('polls', 'type_in_question');
	foreach ($form['answers'] as $row) {
		if (!empty($row)) {
			$check_answers = true;
			break;
		}
	}
	if (!isset($check_answers))
		$errors[] = lang('polls', 'type_in_answer');

	if (isset($errors)) {
		$tpl->assign('error_msg', comboBox($errors));
	} else {
		$start_date = dateAligned(3, array($form['start_hour'], $form['start_min'], 0, $form['start_month'], $form['start_day'], $form['start_year']));
		$end_date = dateAligned(3, array($form['end_hour'], $form['end_min'], 0, $form['end_month'], $form['end_day'], $form['end_year']));

		$insert_values = array(
			'id' => '',
			'start' => $start_date,
			'end' => $end_date,
			'question' => $db->escape($form['question']),
		);

		$bool = $db->insert('poll_question', $insert_values);

		if ($bool) {
			$poll_id = $db->select('id', 'poll_question', 'start = \'' . $start_date . '\' AND end = \'' . $end_date . '\' AND question = \'' . $db->escape($form['question']) . '\'', 'id DESC', 1);
			foreach ($form['answers'] as $row) {
				$insert_answer = array(
					'id' => '',
					'text' => $db->escape($row),
					'poll_id' => $poll_id[0]['id'],
				);
				if (!empty($row) && $bool2 = $db->insert('poll_answers', $insert_answer))
					continue;
			}
		}

		$content = comboBox($bool && $bool2 ? lang('polls', 'create_success') : lang('polls', 'create_error'), uri('acp/polls'));
	}
}
if (!isset($_POST['submit']) || isset($errors) && is_array($errors)) {
	// Datumsauswahl
	$tpl->assign('start_date', publicationPeriod('start'));
	$tpl->assign('end_date', publicationPeriod('end'));

	$tpl->assign('disable', false);
	if (isset($_POST['form']['answers'])) {
		$i = 0;
		foreach ($_POST['form']['answers'] as $row) {
			$answers[$i]['number'] = $i + 1;
			$answers[$i]['value'] = $row;
			$i++;
		}
		if ($validate->countArrayElements($_POST['form']['answers']) <= 9 && !isset($_POST['submit'])) {
			$answers[$i]['number'] = $i + 1;
			$answers[$i]['value'] = '';
		}
		if ($validate->countArrayElements($_POST['form']['answers']) >= 9) {
			$tpl->assign('disable', true);
		}
	} else {
		$answers[0]['number'] = 1;
		$answers[0]['value'] = '';
	}
	$tpl->assign('answers', $answers);

	$tpl->assign('question', isset($_POST['form']['question']) ? $_POST['form']['question'] : '');

	$content = $tpl->fetch('polls/create.html');
}
?>