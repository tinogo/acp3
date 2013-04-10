<?php
/**
 * Polls
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

if (defined('IN_ADM') === false)
	exit;

getRedirectMessage();

$polls = ACP3_CMS::$db2->fetchAll('SELECT id, start, end, title FROM ' . DB_PRE . 'polls ORDER BY start DESC, end DESC, id DESC');
$c_polls = count($polls);

if ($c_polls > 0) {
	$can_delete = ACP3_Modules::check('polls', 'acp_delete');
	$config = array(
		'element' => '#acp-table',
		'sort_col' => $can_delete === true ? 1 : 0,
		'sort_dir' => 'desc',
		'hide_col_sort' => $can_delete === true ? 0 : ''
	);
	ACP3_CMS::$view->setContent(datatable($config));

	for ($i = 0; $i < $c_polls; ++$i) {
		$polls[$i]['period'] = ACP3_CMS::$date->formatTimeRange($polls[$i]['start'], $polls[$i]['end']);
	}
	ACP3_CMS::$view->assign('polls', $polls);
	ACP3_CMS::$view->assign('can_delete', $can_delete);
}
ACP3_CMS::$view->appendContent(ACP3_CMS::$view->fetchTemplate('polls/acp_list.tpl'));