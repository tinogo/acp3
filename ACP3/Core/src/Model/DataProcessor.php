<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Model;

use ACP3\Core\Model\DataProcessor\ColumnTypeStrategyFactory;

class DataProcessor
{
    /**
     * @var ColumnTypeStrategyFactory
     */
    protected $factory;

    /**
     * DataProcessor constructor.
     */
    public function __construct(ColumnTypeStrategyFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return array
     *
     * @deprecated since version 4.33.7, to be removed with version 5.0.0. Use ::escape() instead
     */
    public function processColumnData(array $columnData, array $columnConstraints)
    {
        return $this->escape($columnData, $columnConstraints);
    }

    /**
     * @return array
     */
    public function escape(array $columnData, array $columnConstraints)
    {
        $data = [];
        foreach ($columnData as $column => $value) {
            if (\array_key_exists($column, $columnConstraints)) {
                $data[$column] = $this->factory->getStrategy($columnConstraints[$column])->doEscape($value);
            }
        }

        foreach ($this->findMissingColumns($columnData, $columnConstraints) as $columnName) {
            $data[$columnName] = $this->factory->getStrategy($columnConstraints[$columnName])->getDefaultValue();
        }

        return $data;
    }

    /**
     * @return array
     */
    public function unescape(array $columnData, array $columnConstraints)
    {
        $data = [];
        foreach ($columnData as $column => $value) {
            if (\array_key_exists($column, $columnConstraints)) {
                $data[$column] = $this->factory->getStrategy($columnConstraints[$column])->doUnescape($value);
            }
        }

        foreach ($this->findMissingColumns($columnData, $columnConstraints) as $columnName) {
            $data[$columnName] = $this->factory->getStrategy($columnConstraints[$columnName])->getDefaultValue();
        }

        return $data;
    }

    private function findMissingColumns(array $columnData, array $columnConstraints): array
    {
        $missingColumns = \array_diff(
            \array_keys($columnConstraints),
            \array_intersect(
                \array_keys($columnData),
                \array_keys($columnConstraints)
            )
        );

        return $missingColumns;
    }
}
