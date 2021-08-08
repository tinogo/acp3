<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Repository;

interface RepositoryInterface
{
    /**
     * Executes the SQL insert statement.
     *
     * The method will return the last inserted ID.
     */
    public function insert(array $data): int;

    public function getTableName(string $tableName = ''): string;

    /**
     * Executes the SQL delete statement.
     *
     * @param int|array $entryId
     *
     * @return bool|int
     */
    public function delete($entryId, ?string $columnName = null);

    /**
     * Executes the SQL update statement.
     *
     * @param int|array $entryId
     *
     * @return bool|int
     */
    public function update(array $data, $entryId);

    /**
     * Returns a single full result set by the value of its primary key.
     */
    public function getOneById(int $entryId);
}
