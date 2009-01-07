<?php
/**
 * Breadcrumbs
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Core
 */
/**
 * Erzeugt die Brotkrümelspur und gibt den Titel der jeweiligen Seite aus
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Core
 */
class breadcrumb
{
	/**
	 * Enthält alle Schritte der Brotkrümelspur
	 *
	 * @var array
	 * @access private
	 */
	protected static $steps = array();
	/**
	 * Das Ende der Brotkrümelspur
	 *
	 * @var string
	 * @access private
	 */
	protected static $end = '';

	/**
	 * Zuweisung der jewiligen Stufen der Brotkrümelspur
	 *
	 * @param string $title
	 * 	Bezeichnung der jeweiligen Stufe der Brotkrume
	 * @param string $uri
	 * 	Der zum $title zugehörige Hyperlink
	 *
	 * @return array
	 */
	public static function assign($title, $uri = 0)
	{
		static $i = 0;

		if (!empty($uri)) {
			self::$steps[$i]['uri'] = $uri;
			self::$steps[$i]['title'] = $title;
			$i++;
			return;
		} else {
			self::$end = $title;
			return;
		}
	}
	/**
	 * Gibt je nach Modus entweder die Brotkrümelspur oder den Seitentitel aus
	 *
	 * @param integer $mode
	 * 	1 = Brotkrümelspur ausgeben
	 * 	2 = Nur Seitentitel ausgeben
	 *
	 * @return string
	 */
	public static function output($mode = 1)
	{
		global $db, $lang, $uri, $tpl;

		$module = $uri->mod;
		$page = $uri->page;

		// Frontendbereich
		if (defined('IN_ACP3')) {
			$pages = $db->query('SELECT p.title, p.uri FROM ' . CONFIG_DB_PRE . 'menu_items p, ' . CONFIG_DB_PRE . 'menu_items c WHERE c.left_id BETWEEN p.left_id AND p.right_id AND (c.uri = \'' . $module . '\' OR c.uri = \'' . $uri->query . '\') ORDER BY p.left_id ASC');
			$c_pages = count($pages);

			// Dynamische Seite (ACP3 intern)
			if ($c_pages > 1) {
				// Die durch das Modul festgelegte Brotkrümelspur mit den
				// übergeordneten Menüpunkten verschmelzen
				if ($mode == 1) {
					if (!empty(self::$steps) && !empty(self::$end)) {
						array_unshift(array_slice($pages, 0, -1), self::$steps);
					} else {
						self::$steps = array_slice($pages, 0, -1);
						self::$end = $pages[$c_pages - 1]['title'];
					}
				} else {
					self::$end = $pages[$c_pages - 1]['title'];
				}
			// Brotkümelspur erzeugen, falls keine durch das Modul festgelegt wurde
			} elseif (empty(self::$steps) && empty(self::$end)) {
				self::$end = $page == 'list' ? $lang->t($module, $module) : $lang->t($module, $page);
			}
		// ACP
		} elseif (defined('IN_ADM') && empty(self::$steps) && empty(self::$end)) {
			self::assign($lang->t('common', 'acp'), uri('acp'));
			// Modulindex der jeweiligen ACP-Seite
			if ($page == 'adm_list') {
				self::assign($lang->t($module, $module));
			} else {
				self::assign($lang->t($module, $module), uri('acp/' . $module));
				self::assign($lang->t($module, $page));
			}
		}

		// Brotkrümelspur ausgeben
		if ($mode == 1) {
			$tpl->assign('breadcrumb', self::$steps);
			$tpl->assign('end', self::$end);
			return $tpl->fetch('common/breadcrumb.html');
		}

		// Nur Titel ausgeben
		return self::$end;
	}
}
?>