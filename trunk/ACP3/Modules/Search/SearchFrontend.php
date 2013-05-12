<?php

namespace ACP3\Modules\Search;

use ACP3\Core;

/**
 * Description of SearchFrontend
 *
 * @author Tino
 */
class SearchFrontend extends Core\ModuleController {

	public function __construct($injector)
	{
		parent::__construct($injector);
	}

	public function actionList()
	{
		if (isset($_POST['submit']) === true) {
			if (strlen($_POST['search_term']) < 3)
				$errors['search-term'] = $this->injector['Lang']->t('search', 'search_term_to_short');
			if (empty($_POST['mods']))
				$errors[] = $this->injector['Lang']->t('search', 'no_module_selected');
			if (empty($_POST['area']))
				$errors[] = $this->injector['Lang']->t('search', 'no_area_selected');
			if (empty($_POST['sort']) || $_POST['sort'] != 'asc' && $_POST['sort'] != 'desc')
				$errors[] = $this->injector['Lang']->t('search', 'no_sorting_selected');

			if (isset($errors) === true) {
				$this->injector['View']->assign('error_msg', Core\Functions::errorBox($errors));
			} else {
				$this->injector['Breadcrumb']
						->append($this->injector['Lang']->t('search', 'search'), $this->injector['URI']->route('search'))
						->append($this->injector['Lang']->t('search', 'search_results'));

				$_POST['search_term'] = Core\Functions::str_encode($_POST['search_term']);
				$_POST['sort'] = strtoupper($_POST['sort']);
				$results_mods = array();
				foreach ($_POST['mods'] as $module) {
					if (Core\Modules::check($module, 'extensions/search') === true) {
						include_once MODULES_DIR . $module . '/extensions/search.php';
					}
				}
				if (!empty($results_mods)) {
					ksort($results_mods);
					$this->injector['View']->assign('results_mods', $results_mods);
				} else {
					$this->injector['View']->assign('no_search_results', sprintf($this->injector['Lang']->t('search', 'no_search_results'), $_POST['search_term']));
				}

				$this->injector['View']->setContentTemplate('search/results.tpl');
			}
		}
		if (isset($_POST['submit']) === false || isset($errors) === true && is_array($errors) === true) {
			$this->injector['View']->assign('form', isset($_POST['submit']) ? $_POST : array('search_term' => ''));

			$mods = scandir(MODULES_DIR);
			$c_mods = count($mods);
			$search_mods = array();

			for ($i = 0; $i < $c_mods; ++$i) {
				if (Core\Modules::check($mods[$i], 'extensions/search') === true) {
					$info = Core\Modules::getModuleInfo($mods[$i]);
					$name = $info['name'];
					$search_mods[$name]['dir'] = $mods[$i];
					$search_mods[$name]['checked'] = Core\Functions::selectEntry('mods', $mods[$i], $mods[$i], 'checked');
					$search_mods[$name]['name'] = $name;
				}
			}
			ksort($search_mods);
			$this->injector['View']->assign('search_mods', $search_mods);

			// Zu durchsuchende Bereiche
			$lang_search_areas = array(
				$this->injector['Lang']->t('search', 'title_only'),
				$this->injector['Lang']->t('search', 'content_only'),
				$this->injector['Lang']->t('search', 'title_and_content')
			);
			$this->injector['View']->assign('search_areas', Core\Functions::selectGenerator('area', array('title', 'content', 'title_content'), $lang_search_areas, 'title', 'checked'));

			// Treffer sortieren
			$lang_sort_hits = array($this->injector['Lang']->t('search', 'asc'), $this->injector['Lang']->t('search', 'desc'));
			$this->injector['View']->assign('sort_hits', Core\Functions::selectGenerator('sort', array('asc', 'desc'), $lang_sort_hits, 'asc', 'checked'));
		}
	}

	public function actionSidebar()
	{
		$mods = scandir(MODULES_DIR);
		$c_mods = count($mods);
		$search_mods = array();

		for ($i = 0; $i < $c_mods; ++$i) {
			if ($mods[$i] !== '.' && $mods[$i] !== '..' && Core\Modules::check($mods[$i], 'extensions/search') === true) {
				$info = Core\Modules::getModuleInfo($mods[$i]);
				$name = $info['name'];
				$search_mods[$name]['dir'] = $mods[$i];
				$search_mods[$name]['checked'] = Core\Functions::selectEntry('mods', $mods[$i], $mods[$i], 'checked');
				$search_mods[$name]['name'] = $name;
			}
		}
		ksort($search_mods);
		$this->injector['View']->assign('search_mods', $search_mods);

		$this->injector['View']->displayTemplate('search/sidebar.tpl');
	}

}