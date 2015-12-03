<?php

namespace ACP3\Modules\ACP3\Search\Controller;

use ACP3\Core;
use ACP3\Modules\ACP3\Search;

/**
 * Class Index
 * @package ACP3\Modules\ACP3\Search\Controller
 */
class Index extends Core\Modules\FrontendController
{
    /**
     * @var \ACP3\Modules\ACP3\Search\Helpers
     */
    protected $searchHelpers;
    /**
     * @var \ACP3\Modules\ACP3\Search\Validation\Validator
     */
    protected $searchValidator;

    /**
     * @param \ACP3\Core\Modules\Controller\FrontendContext  $context
     * @param \ACP3\Modules\ACP3\Search\Helpers              $searchHelpers
     * @param \ACP3\Modules\ACP3\Search\Validation\Validator $searchValidator
     */
    public function __construct(
        Core\Modules\Controller\FrontendContext $context,
        Search\Helpers $searchHelpers,
        Search\Validation\Validator $searchValidator)
    {
        parent::__construct($context);

        $this->searchHelpers = $searchHelpers;
        $this->searchValidator = $searchValidator;
    }

    /**
     * @param string $q
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function actionIndex($q = '')
    {
        if ($this->request->getPost()->isEmpty() === false) {
            return $this->_indexPost($this->request->getPost()->all());
        } elseif (!empty($q)) {
            return $this->_indexPost(['search_term' => (string)$q]);
        }

        // Zu durchsuchende Bereiche
        $langSearchAreas = [
            $this->lang->t('search', 'title_and_content'),
            $this->lang->t('search', 'title_only'),
            $this->lang->t('search', 'content_only')
        ];

        // Treffer sortieren
        $langSortHits = [$this->lang->t('search', 'asc'), $this->lang->t('search', 'desc')];

        return [
            'form' => array_merge(['search_term' => ''], $this->request->getPost()->all()),
            'search_mods' => $this->searchHelpers->getModules(),
            'search_areas' => $this->get('core.helpers.forms')->checkboxGenerator(
                'area',
                ['title_content', 'title', 'content'],
                $langSearchAreas,
                'title_content'
            ),
            'sort_hits' => $this->get('core.helpers.forms')->checkboxGenerator('sort', ['asc', 'desc'], $langSortHits, 'asc')
        ];
    }

    /**
     * @param array  $modules
     * @param string $searchTerm
     * @param string $area
     * @param string $sort
     */
    protected function _displaySearchResults(array $modules, $searchTerm, $area, $sort)
    {
        $this->breadcrumb
            ->append($this->lang->t('search', 'search'), 'search')
            ->append($this->lang->t('search', 'search_results'));

        $searchResultsEvent = new Search\Event\DisplaySearchResults($modules, $searchTerm, $area, $sort);
        $this->eventDispatcher->dispatch('search.events.displaySearchResults', $searchResultsEvent);

        $searchResults = $searchResultsEvent->getSearchResults();
        if (!empty($searchResults)) {
            ksort($searchResults);
            $this->view->assign('results_mods', $searchResults);
        } else {
            $this->view->assign('no_search_results', sprintf($this->lang->t('search', 'no_search_results'), $searchTerm));
        }

        $this->setTemplate('Search/Frontend/index.results.tpl');
    }

    /**
     * @param array $formData
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function _indexPost(array $formData)
    {
        return $this->actionHelper->handlePostAction(
            function () use ($formData) {
                if (isset($formData['search_term']) === true) {
                    if (isset($formData['mods']) === false) {
                        $modules = $this->searchHelpers->getModules();

                        $formData['mods'] = [];
                        foreach ($modules as $row) {
                            $formData['mods'][] = $row['dir'];
                        }
                    }
                    if (isset($formData['area']) === false) {
                        $formData['area'] = 'title_content';
                    }
                    if (isset($formData['sort']) === false) {
                        $formData['sort'] = 'asc';
                    }
                }

                $this->searchValidator->validate($formData);

                $this->_displaySearchResults(
                    $formData['mods'],
                    Core\Functions::strEncode($formData['search_term']),
                    $formData['area'],
                    strtoupper($formData['sort'])
                );
            }
        );
    }
}
