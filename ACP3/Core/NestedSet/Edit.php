<?php
namespace ACP3\Core\NestedSet;

/**
 * Class Edit
 * @package ACP3\Core\NestedSet
 */
class Edit extends AbstractNestedSetOperation
{
    /**
     * Methode zum Bearbeiten eines Knotens
     *
     * @param integer $id
     *    ID des zu bearbeitenden Knotens
     * @param integer $parentId
     *    ID des neuen Elternelements
     * @param integer $blockId
     *    ID des neuen Blocks
     * @param array   $updateValues
     *
     * @return boolean
     */
    public function execute($id, $parentId, $blockId, array $updateValues)
    {
        $this->db->getConnection()->beginTransaction();
        try {
            $nodes = $this->nestedSetModel->fetchNodeWithSiblings($this->tableName, $id, $this->enableBlocks);

            // Überprüfen, ob Seite ein Root-Element ist und ob dies auch so bleiben soll
            if ($this->nodeIsRootItemAndNoChangeNeed($parentId, $blockId, $nodes[0])) {
                $bool = $this->db->getConnection()->update($this->tableName, $updateValues, ['id' => $id]);
            } else {
                $currentParent = $this->nestedSetModel->fetchParentNode(
                    $this->tableName,
                    $nodes[0]['left_id'],
                    $nodes[0]['right_id']
                );

                // Überprüfung, falls Seite kein Root-Element ist und auch keine Veränderung vorgenommen werden soll...
                if (!empty($currentParent) && $currentParent == $parentId) {
                    $bool = $this->db->getConnection()->update($this->tableName, $updateValues, ['id' => $id]);
                } else { // ...ansonsten den Baum bearbeiten...
                    // Differenz zwischen linken und rechten Wert bilden
                    $itemDiff = $nodes[0]['right_id'] - $nodes[0]['left_id'] + 1;

                    // Neues Elternelement
                    $newParent = $this->nestedSetModel->fetchNodeById($this->tableName, $parentId);

                    // Knoten werden eigenes Root-Element
                    if (empty($newParent)) {
                        list($rootId, $diff) = $this->nodeBecomesRootNode($id, $blockId, $nodes, $itemDiff);
                    } else { // Knoten werden Kinder von einem anderen Knoten
                        // Teilbaum nach unten...
                        if ($newParent['left_id'] > $nodes[0]['left_id']) {
                            $newParent['left_id'] -= $itemDiff;
                            $newParent['right_id'] -= $itemDiff;
                        }

                        $diff = $newParent['left_id'] - $nodes[0]['left_id'] + 1;
                        $rootId = $newParent['root_id'];

                        $this->adjustParentNodesAfterSeparation($itemDiff, $nodes[0]['left_id'], $nodes[0]['right_id']);
                        $this->adjustFollowingNodesAfterSeparation($itemDiff, $nodes[0]['right_id']);
                        $this->adjustParentNodesAfterInsert($itemDiff, $newParent['left_id'], $newParent['right_id']);
                        $this->adjustFollowingNodesAfterInsert($itemDiff, $newParent['left_id'] + 1);
                    }

                    $bool = $this->adjustNodeSiblings($blockId, $nodes, $diff, $rootId);

                    $this->db->getConnection()->update($this->tableName, $updateValues, ['id' => $id]);
                    $this->db->getConnection()->commit();
                }
            }
            return $bool;
        } catch (\Exception $e) {
            $this->db->getConnection()->rollback();
            return false;
        }
    }

    /**
     * @param int   $parentId
     * @param int   $blockId
     * @param array $items
     *
     * @return bool
     */
    protected function nodeIsRootItemAndNoChangeNeed($parentId, $blockId, array $items)
    {
        return empty($parentId) &&
        ($this->enableBlocks === false || ($this->enableBlocks === true && $blockId == $items['block_id'])) &&
        $this->nestedSetModel->nodeIsRootItem($this->tableName, $items['left_id'], $items['right_id']) === true;
    }

    /**
     * @param int   $id
     * @param int   $blockId
     * @param array $nodes
     * @param int   $itemDiff
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function nodeBecomesRootNode($id, $blockId, array $nodes, $itemDiff)
    {
        $rootId = $id;
        if ($this->enableBlocks === true) {
            // Knoten in anderen Block verschieben
            if ($nodes[0]['block_id'] != $blockId) {
                $diff = $this->nodeBecomesRootNodeInNewBlock($blockId, $nodes, $itemDiff);
            } else { // Element zum neuen Wurzelknoten machen
                $diff = $this->nodeBecomesRootNodeInSameBlock($nodes, $itemDiff);
            }
        } else {
            $maxId = $this->nestedSetModel->fetchMaximumRightId($this->tableName);
            $diff = $maxId - $nodes[0]['right_id'];

            $this->adjustParentNodesAfterSeparation($itemDiff, $nodes[0]['left_id'], $nodes[0]['right_id']);
            $this->adjustFollowingNodesAfterSeparation($itemDiff, $nodes[0]['right_id']);
        }

        return [$rootId, $diff];
    }

    /**
     * @param int   $blockId
     * @param array $nodes
     * @param int   $itemDiff
     *
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function nodeBecomesRootNodeInNewBlock($blockId, array $nodes, $itemDiff)
    {
        $newBlockLeftId = $this->nestedSetModel->fetchMinimumLeftIdByBlockId($this->tableName, $blockId);

        // Falls die Knoten in einen leeren Block verschoben werden sollen,
        // die right_id des letzten Elementes verwenden
        if (empty($newBlockLeftId) || is_null($newBlockLeftId) === true) {
            $newBlockLeftId = $this->nestedSetModel->fetchMaximumRightId($this->tableName);
            $newBlockLeftId += 1;
        }

        if ($blockId > $nodes[0]['block_id']) {
            $newBlockLeftId -= $itemDiff;
        }

        $this->adjustParentNodesAfterSeparation($itemDiff, $nodes[0]['left_id'], $nodes[0]['right_id']);
        $this->adjustFollowingNodesAfterSeparation($itemDiff, $nodes[0]['right_id']);
        $this->adjustFollowingNodesAfterInsert($itemDiff, $newBlockLeftId);

        return $newBlockLeftId - $nodes[0]['left_id'];
    }

    /**
     * @param array $nodes
     * @param int   $itemDiff
     *
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function nodeBecomesRootNodeInSameBlock(array $nodes, $itemDiff)
    {
        $maxId = $this->nestedSetModel->fetchMaximumRightIdByBlockId($this->tableName, $nodes[0]['block_id']);

        $this->adjustParentNodesAfterSeparation($itemDiff, $nodes[0]['left_id'], $nodes[0]['right_id']);

        $this->db->getConnection()->executeUpdate(
            "UPDATE {$this->tableName} SET left_id = left_id - ?, right_id = right_id - ? WHERE left_id > ? AND block_id = ?",
            [$itemDiff, $itemDiff, $nodes[0]['right_id'], $nodes[0]['block_id']]
        );

        return $maxId - $nodes[0]['right_id'];
    }

    /**
     * @param int   $blockId
     * @param array $nodes
     * @param int   $diff
     * @param int   $rootId
     *
     * @return int|bool
     * @throws \Doctrine\DBAL\DBALException
     */
    private function adjustNodeSiblings($blockId, array $nodes, $diff, $rootId)
    {
        $bool = false;

        foreach ($nodes as $node) {
            $node['left_id'] += $diff;
            $node['right_id'] += $diff;

            $parentId = $this->nestedSetModel->fetchParentNode(
                $this->tableName,
                $node['left_id'],
                $node['right_id']
            );
            if ($this->enableBlocks === true) {
                $bool = $this->db->getConnection()->executeUpdate(
                    "UPDATE {$this->tableName} SET block_id = ?, root_id = ?, parent_id = ?, left_id = ?, right_id = ? WHERE id = ?",
                    [
                        $blockId,
                        $rootId,
                        $parentId,
                        $node['left_id'],
                        $node['right_id'],
                        $node['id']
                    ]
                );
            } else {
                $bool = $this->db->getConnection()->executeUpdate(
                    "UPDATE {$this->tableName} SET root_id = ?, parent_id = ?, left_id = ?, right_id = ? WHERE id = ?",
                    [
                        $rootId,
                        $parentId,
                        $node['left_id'],
                        $node['right_id'],
                        $node['id']
                    ]
                );
            }
            if ($bool === false) {
                break;
            }
        }
        return $bool;
    }
}