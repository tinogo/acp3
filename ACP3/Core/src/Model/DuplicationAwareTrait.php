<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Model;

use ACP3\Core\Repository\AbstractRepository;

trait DuplicationAwareTrait
{
    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function duplicate(int $entryId): bool|int
    {
        $resultSet = $this->getRepository()->getOneById($entryId);

        if (!empty($resultSet)) {
            $data = $this->getDataProcessor()->unescape($resultSet, $this->getAllowedColumns());

            return $this->save(array_merge($data, $this->getDefaultDataForDuplication()));
        }

        return false;
    }

    /**
     * @return AbstractRepository
     */
    abstract protected function getRepository();

    abstract protected function getDataProcessor(): DataProcessor;

    /**
     * @return array
     */
    abstract protected function getAllowedColumns();

    /**
     * @param int|null $entryId
     */
    abstract public function save(array $rawData, $entryId = null): int;

    /**
     * @return array
     */
    protected function getDefaultDataForDuplication()
    {
        return [];
    }
}
