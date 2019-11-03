<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Helpers;

use ACP3\Core\ACL;
use ACP3\Core\DataGrid\ConfigProcessor;
use ACP3\Core\DataGrid\Input;
use ACP3\Core\Helpers\DataGrid\ColumnPriorityQueue;
use ACP3\Core\Helpers\DataGrid\ColumnRenderer\ColumnRendererInterface;
use ACP3\Core\Helpers\DataGrid\ColumnRenderer\HeaderColumnRenderer;
use ACP3\Core\Helpers\DataGrid\ColumnRenderer\MassActionColumnRenderer;
use ACP3\Core\Helpers\DataGrid\ColumnRenderer\OptionColumnRenderer;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Model\Repository\DataGridRepository;

/**
 * @deprecated Since version 4.30.0, to be removed in 5.0.0. Use class ACP3\Core\DataGrid\DataGrid instead
 */
class DataGrid
{
    /**
     * @var \ACP3\Core\Model\Repository\DataGridRepository
     */
    protected $repository;
    /**
     * @var array
     */
    protected $results = [];
    /**
     * @var string|null
     */
    protected $resourcePathEdit;
    /**
     * @var string|null
     */
    protected $resourcePathDelete;
    /**
     * @var string|null
     */
    protected $identifier;
    /**
     * @var int
     */
    protected $recordsPerPage = 10;
    /**
     * @var bool
     */
    protected $enableMassAction = true;
    /**
     * @var bool
     */
    protected $enableOptions = true;
    /**
     * @var \ACP3\Core\Helpers\DataGrid\ColumnPriorityQueue
     */
    protected $columns;
    /**
     * @var string|null
     */
    private $primaryKey;
    /**
     * @var array
     */
    protected $columnRenderer = [];
    /**
     * @var \ACP3\Core\ACL
     */
    protected $acl;
    /**
     * @var \ACP3\Core\I18n\Translator
     */
    protected $translator;
    /**
     * @var \ACP3\Core\DataGrid\ConfigProcessor
     */
    private $configProcessor;

    public function __construct(
        ACL $acl,
        Translator $translator,
        ConfigProcessor $configProcessor
    ) {
        $this->acl = $acl;
        $this->translator = $translator;
        $this->columns = new ColumnPriorityQueue();
        $this->configProcessor = $configProcessor;
    }

    /**
     * @return $this
     */
    public function registerColumnRenderer(ColumnRendererInterface $columnRenderer)
    {
        $this->columnRenderer[\get_class($columnRenderer)] = $columnRenderer;

        return $this;
    }

    /**
     * @return $this
     */
    public function setRepository(DataGridRepository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @param array $results
     *
     * @return $this
     */
    public function setResults($results)
    {
        $this->results = $results;

        return $this;
    }

    /**
     * @param string $resourcePathEdit
     *
     * @return $this
     */
    public function setResourcePathEdit($resourcePathEdit)
    {
        $this->resourcePathEdit = $resourcePathEdit;

        return $this;
    }

    /**
     * @param string $resourcePathDelete
     *
     * @return $this
     */
    public function setResourcePathDelete($resourcePathDelete)
    {
        $this->resourcePathDelete = $resourcePathDelete;

        return $this;
    }

    /**
     * @param int $recordsPerPage
     *
     * @return $this
     */
    public function setRecordsPerPage($recordsPerPage)
    {
        $this->recordsPerPage = (int) $recordsPerPage;

        return $this;
    }

    /**
     * @param bool $enableMassAction
     *
     * @return $this
     */
    public function setEnableMassAction($enableMassAction)
    {
        $this->enableMassAction = (bool) $enableMassAction;

        return $this;
    }

    /**
     * @param bool $enableOptions
     *
     * @return $this
     */
    public function setEnableOptions($enableOptions)
    {
        $this->enableOptions = (bool) $enableOptions;

        return $this;
    }

    /**
     * @param string $identifier
     *
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function addColumn(array $columnData, $priority)
    {
        $columnData = \array_merge(
            [
                'label' => '',
                'type' => '',
                'fields' => [],
                'class' => '',
                'style' => '',
                'sortable' => true,
                'default_sort' => false,
                'default_sort_direction' => 'asc',
                'custom' => [],
                'attribute' => [],
                'primary' => false,
            ],
            $columnData
        );

        $this->columns->insert($columnData, $priority);

        return $this;
    }

    /**
     * @return array
     */
    public function render()
    {
        $canDelete = $this->acl->hasPermission($this->resourcePathDelete);
        $canEdit = $this->acl->hasPermission($this->resourcePathEdit);

        $this->addDefaultColumns($canDelete, $canEdit);
        $this->findPrimaryKey();

        $input = (new Input())
            ->setUseAjax(false)
            ->setIdentifier($this->identifier);

        foreach (clone $this->columns as $key => $column) {
            $input->addColumn($column, $key * 10);
        }

        return [
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
            'identifier' => \substr($this->identifier, 1),
            'header' => $this->renderTableHeader(),
            'config' => $this->configProcessor->generateDataTableConfig($input),
            'results' => $this->mapTableColumnsToDbFields(),
            'num_results' => $this->countDbResults(),
        ];
    }

    /**
     * @return string
     */
    protected function renderTableHeader()
    {
        $header = '';
        foreach (clone $this->columns as $column) {
            if (!empty($column['label'])) {
                $header .= $this->columnRenderer[HeaderColumnRenderer::class]
                    ->setIdentifier($this->identifier)
                    ->setPrimaryKey($this->primaryKey)
                    ->fetchDataAndRenderColumn($column, []);
            }
        }

        return $header;
    }

    /**
     * @return string
     */
    protected function mapTableColumnsToDbFields()
    {
        $renderedResults = '';
        foreach ($this->fetchDbResults() as $result) {
            $renderedResults .= '<tr>';
            foreach (clone $this->columns as $column) {
                if (\array_key_exists($column['type'], $this->columnRenderer) && !empty($column['label'])) {
                    $renderedResults .= $this->columnRenderer[$column['type']]
                        ->setIdentifier($this->identifier)
                        ->setPrimaryKey($this->primaryKey)
                        ->fetchDataAndRenderColumn($column, $result);
                }
            }

            $renderedResults .= "</tr>\n";
        }

        return $renderedResults;
    }

    /**
     * @return array
     */
    protected function generateDataTableConfig()
    {
        $columnDefinitions = [];
        $i = 0;

        $defaultSortColumn = $defaultSortDirection = null;
        foreach (clone $this->columns as $column) {
            if ($column['sortable'] === false) {
                $columnDefinitions[] = $i;
            }

            if ($column['default_sort'] === true &&
                \in_array($column['default_sort_direction'], ['asc', 'desc'])
            ) {
                $defaultSortColumn = $i;
                $defaultSortDirection = $column['default_sort_direction'];
            }

            if (!empty($column['label'])) {
                ++$i;
            }
        }

        return [
            'element' => $this->identifier,
            'records_per_page' => $this->recordsPerPage,
            'hide_col_sort' => \implode(', ', $columnDefinitions),
            'sort_col' => $defaultSortColumn,
            'sort_dir' => $defaultSortDirection,
        ];
    }

    /**
     * @param bool $canDelete
     * @param bool $canEdit
     */
    protected function addDefaultColumns($canDelete, $canEdit)
    {
        if ($this->enableMassAction && $canDelete) {
            $this->addColumn([
                'label' => $this->identifier,
                'type' => MassActionColumnRenderer::class,
                'class' => 'datagrid-column datagrid-column__mass-action',
                'sortable' => false,
                'custom' => [
                    'can_delete' => $canDelete,
                ],
            ], 1000);
        }

        if ($this->enableOptions) {
            $this->addColumn([
                'label' => $this->translator->t('system', 'action'),
                'type' => OptionColumnRenderer::class,
                'class' => 'datagrid-column datagrid-column__actions',
                'sortable' => false,
                'custom' => [
                    'can_delete' => $canDelete,
                    'can_edit' => $canEdit,
                    'resource_path_delete' => $this->resourcePathDelete,
                    'resource_path_edit' => $this->resourcePathEdit,
                ],
            ], 0);
        }
    }

    /**
     * Finds the primary key column.
     */
    protected function findPrimaryKey()
    {
        foreach (clone $this->columns as $column) {
            if ($column['primary'] === true && !empty($column['fields'])) {
                $this->primaryKey = \reset($column['fields']);

                break;
            }
        }
    }

    /**
     * @return array
     */
    protected function fetchDbResults()
    {
        if (empty($this->results) && $this->repository instanceof DataGridRepository) {
            $this->results = $this->repository->getAll(clone $this->columns);
        }

        return $this->results;
    }

    /**
     * @return int
     */
    public function countDbResults()
    {
        return \count($this->fetchDbResults());
    }
}
