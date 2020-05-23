<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Files\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Helpers\ResultsPerPage;
use ACP3\Modules\ACP3\Files;
use ACP3\Modules\ACP3\Files\Helpers;
use ACP3\Modules\ACP3\System\Installer\Schema;

class Index extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var \ACP3\Modules\ACP3\Files\Model\Repository\DataGridRepository
     */
    protected $dataGridRepository;
    /**
     * @var \ACP3\Core\DataGrid\DataGrid
     */
    private $dataGrid;
    /**
     * @var \ACP3\Core\ACL
     */
    private $acl;
    /**
     * @var \ACP3\Core\Helpers\ResultsPerPage
     */
    private $resultsPerPage;

    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\ACL $acl,
        ResultsPerPage $resultsPerPage,
        Files\Model\Repository\DataGridRepository $dataGridRepository,
        Core\DataGrid\DataGrid $dataGrid
    ) {
        parent::__construct($context);

        $this->dataGridRepository = $dataGridRepository;
        $this->dataGrid = $dataGrid;
        $this->acl = $acl;
        $this->resultsPerPage = $resultsPerPage;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $input = (new Core\DataGrid\Input())
            ->setUseAjax(true)
            ->setRepository($this->dataGridRepository)
            ->setRecordsPerPage($this->resultsPerPage->getResultsPerPage(Schema::MODULE_NAME))
            ->setIdentifier('#files-data-grid')
            ->setResourcePathDelete('admin/files/index/delete')
            ->setResourcePathEdit('admin/files/index/edit');

        $this->addDataGridColumns($input);

        return $this->dataGrid->render($input);
    }

    /**
     * @param \ACP3\Core\DataGrid\Input $input
     */
    protected function addDataGridColumns(Core\DataGrid\Input $input)
    {
        $settings = $this->config->getSettings(Files\Installer\Schema::MODULE_NAME);

        $input
            ->addColumn([
                'label' => $this->translator->t('files', 'active'),
                'type' => Core\DataGrid\ColumnRenderer\ReplaceValueColumnRenderer::class,
                'fields' => ['active'],
                'custom' => [
                    'search' => [0, 1],
                    'replace' => [$this->translator->t('system', 'no'), $this->translator->t('system', 'yes')],
                ],
            ], 60)
            ->addColumn([
                'label' => $this->translator->t('system', 'publication_period'),
                'type' => Core\DataGrid\ColumnRenderer\DateColumnRenderer::class,
                'fields' => ['start', 'end'],
                'default_sort' => $settings['order_by'] === 'date',
                'default_sort_direction' => 'desc',
            ], 50)
            ->addColumn([
                'label' => $this->translator->t('files', 'title'),
                'type' => Core\DataGrid\ColumnRenderer\TextColumnRenderer::class,
                'fields' => ['title'],
            ], 40)
            ->addColumn([
                'label' => $this->translator->t('system', 'description'),
                'type' => Core\DataGrid\ColumnRenderer\TextColumnRenderer::class,
                'fields' => ['text'],
            ], 30)
            ->addColumn([
                'label' => $this->translator->t('files', 'filesize'),
                'type' => Core\DataGrid\ColumnRenderer\TextColumnRenderer::class,
                'fields' => ['size'],
                'customer' => [
                    'default_value' => $this->translator->t('files', 'unknown_filesize'),
                ],
            ], 20)
            ->addColumn([
                'label' => $this->translator->t('system', 'id'),
                'type' => Core\DataGrid\ColumnRenderer\RouteColumnRenderer::class,
                'fields' => ['id'],
                'primary' => true,
                'custom' => [
                    'path' => Helpers::URL_KEY_PATTERN,
                ],
            ], 10);

        if ($settings['order_by'] === 'custom' && $this->acl->hasPermission('admin/files/index/sort')) {
            $input
                ->addColumn([
                    'label' => $this->translator->t('system', 'order'),
                    'type' => Core\DataGrid\ColumnRenderer\SortColumnRenderer::class,
                    'fields' => ['sort'],
                    'default_sort' => $settings['order_by'] === 'custom',
                    'custom' => [
                        'route_sort_down' => 'acp/files/index/sort/id_%d/action_down',
                        'route_sort_up' => 'acp/files/index/sort/id_%d/action_up',
                    ],
                ], 15);
        }
    }
}
