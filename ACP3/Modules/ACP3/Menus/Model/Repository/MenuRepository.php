<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Model\Repository;

use ACP3\Core;

class MenuRepository extends Core\Model\Repository\AbstractRepository
{
    const TABLE_NAME = 'menus';

    /**
     * @param int $menuId
     *
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function menuExists(int $menuId)
    {
        return (int) $this->db->fetchColumn("SELECT COUNT(*) FROM {$this->getTableName()} WHERE id = :id", ['id' => $menuId]) > 0;
    }

    /**
     * @param string   $menuName
     * @param int|null $menuId
     *
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function menuExistsByName(string $menuName, ?int $menuId = null)
    {
        $where = !empty($menuId) ? ' AND id != :id' : '';

        return (int) $this->db->fetchColumn("SELECT COUNT(*) FROM {$this->getTableName()} WHERE index_name = :indexName" . $where, ['indexName' => $menuName, 'id' => $menuId]) > 0;
    }

    /**
     * @param int $menuId
     *
     * @return bool|string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMenuNameById(int $menuId)
    {
        return $this->db->fetchColumn(
            "SELECT `index_name` FROM {$this->getTableName()} WHERE id = ?",
            [$menuId]
        );
    }

    /**
     * @param int|null $limitStart
     * @param int|null $resultsPerPage
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAllMenus(?int $limitStart = null, ?int $resultsPerPage = null)
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->getTableName()} ORDER BY title ASC, id ASC" .
            $this->buildLimitStmt($limitStart, $resultsPerPage)
        );
    }
}
