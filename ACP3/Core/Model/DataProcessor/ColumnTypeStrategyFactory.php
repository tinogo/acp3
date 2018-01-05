<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Model\DataProcessor;

use ACP3\Core\Model\DataProcessor\ColumnType\ColumnTypeStrategyInterface;

class ColumnTypeStrategyFactory
{
    /**
     * @var ColumnTypeStrategyInterface[]
     */
    protected $columnTypes = [];

    /**
     * @param ColumnTypeStrategyInterface $columnTypeStrategy
     * @param $columnTypeName
     * @return $this
     */
    public function registerColumnType(ColumnTypeStrategyInterface $columnTypeStrategy, $columnTypeName)
    {
        $this->columnTypes[$columnTypeName] = $columnTypeStrategy;

        return $this;
    }

    /**
     * @param string $columnType
     * @return ColumnTypeStrategyInterface
     */
    public function getStrategy($columnType)
    {
        if (!isset($this->columnTypes[$columnType])) {
            throw new \InvalidArgumentException('Invalid column type constraint given: ' . $columnType);
        }

        return $this->columnTypes[$columnType];
    }
}
