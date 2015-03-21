<?php

namespace ACP3\Modules\ACP3\Guestbook;

use ACP3\Core;

/**
 * Class Model
 * @package ACP3\Modules\ACP3\Guestbook
 */
class Model extends Core\Model
{
    const TABLE_NAME = 'guestbook';

    /**
     * @param $id
     * @return bool
     */
    public function resultExists($id)
    {
        return ((int)$this->db->fetchColumn('SELECT COUNT(*) FROM ' . $this->db->getPrefix() . static::TABLE_NAME . ' WHERE id = :id', ['id' => $id]) > 0);
    }

    /**
     * @param $id
     * @return array
     */
    public function getOneById($id)
    {
        return $this->db->fetchAssoc('SELECT * FROM ' . $this->db->getPrefix() . static::TABLE_NAME . ' WHERE id = ?', [$id]);
    }

    /**
     * @param string $notify
     * @return int
     */
    public function countAll($notify = '')
    {
        return count($this->getAll($notify));
    }

    /**
     * @param string $notify
     * @param string $limitStart
     * @param string $resultsPerPage
     * @return array
     */
    public function getAll($notify = '', $limitStart = '', $resultsPerPage = '')
    {
        $where = ($notify == 2) ? 'WHERE active = 1' : '';
        $limitStmt = $this->_buildLimitStmt($limitStart, $resultsPerPage);
        return $this->db->fetchAll('SELECT u.id AS user_id_real, u.nickname AS user_name, u.website AS user_website, u.mail AS user_mail, g.* FROM ' . $this->db->getPrefix() . static::TABLE_NAME . ' AS g LEFT JOIN ' . $this->db->getPrefix() . \ACP3\Modules\ACP3\Users\Model::TABLE_NAME . ' AS u ON(u.id = g.user_id) ' . $where . ' ORDER BY date DESC' . $limitStmt);
    }

    /**
     * @param $ipAddress
     * @return mixed
     */
    public function getLastDateFromIp($ipAddress)
    {
        return $this->db->fetchColumn('SELECT MAX(date) FROM ' . $this->db->getPrefix() . static::TABLE_NAME . ' WHERE ip = ?', [$ipAddress]);
    }

    /**
     * @return array
     */
    public function getAllInAcp()
    {
        return $this->db->fetchAll('SELECT * FROM ' . $this->db->getPrefix() . static::TABLE_NAME . ' ORDER BY date DESC, id DESC');
    }
}
