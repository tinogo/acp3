<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\NestedSet\Operation;

use ACP3\Core\Database\Connection;
use ACP3\Core\NestedSet\Model\Repository\BlockAwareNestedSetRepositoryInterface;
use ACP3\Core\NestedSet\Model\Repository\NestedSetRepository;

abstract class AbstractOperation
{
    /**
     * @var \ACP3\Core\Database\Connection
     */
    protected $db;
    /**
     * @var \ACP3\Core\NestedSet\Model\Repository\NestedSetRepository|BlockAwareNestedSetRepositoryInterface
     */
    protected $nestedSetRepository;
    /**
     * @var bool
     *
     * @deprecated since version 4.30.0, to be removed with 5.0.0. Use method ::isBlockAware() instead
     */
    protected $isBlockAware;

    public function __construct(
        Connection $db,
        NestedSetRepository $nestedSetRepository
    ) {
        $this->db = $db;
        $this->nestedSetRepository = $nestedSetRepository;
        $this->isBlockAware = $this->isBlockAware();
    }

    /**
     * Returns, whether the data repository is aware of the block handling.
     */
    protected function isBlockAware(): bool
    {
        return $this->nestedSetRepository instanceof BlockAwareNestedSetRepositoryInterface;
    }

    /**
     * @param int $diff
     * @param int $leftId
     * @param int $rightId
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function adjustParentNodesAfterSeparation($diff, $leftId, $rightId)
    {
        $this->db->getConnection()->executeUpdate(
            "UPDATE {$this->nestedSetRepository->getTableName()} SET right_id = right_id - ? WHERE left_id < ? AND right_id > ?",
            [$diff, $leftId, $rightId]
        );
    }

    /**
     * @param int $diff
     * @param int $leftId
     * @param int $rightId
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function adjustParentNodesAfterInsert($diff, $leftId, $rightId)
    {
        $this->db->getConnection()->executeUpdate(
            "UPDATE {$this->nestedSetRepository->getTableName()} SET right_id = right_id + ? WHERE left_id <= ? AND right_id >= ?",
            [$diff, $leftId, $rightId]
        );
    }

    /**
     * @param int $diff
     * @param int $leftId
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function adjustFollowingNodesAfterSeparation($diff, $leftId)
    {
        $this->db->getConnection()->executeUpdate(
            "UPDATE {$this->nestedSetRepository->getTableName()} SET left_id = left_id - ?, right_id = right_id - ? WHERE left_id > ?",
            [$diff, $diff, $leftId]
        );
    }

    /**
     * @param int $diff
     * @param int $leftId
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function adjustFollowingNodesAfterInsert($diff, $leftId)
    {
        $this->db->getConnection()->executeUpdate(
            "UPDATE {$this->nestedSetRepository->getTableName()} SET left_id = left_id + ?, right_id = right_id + ? WHERE left_id >= ?",
            [$diff, $diff, $leftId]
        );
    }
}
