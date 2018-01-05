<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Permissions\Validation\ValidationRules;

use ACP3\Core\ACL\PermissionEnum;
use ACP3\Core\Validation\ValidationRules\AbstractValidationRule;
use ACP3\Modules\ACP3\Permissions\Model\Repository\AclPrivilegesRepository;

class PrivilegesExistValidationRule extends AbstractValidationRule
{
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Model\Repository\AclPrivilegesRepository
     */
    protected $privilegeRepository;

    /**
     * PrivilegesExistValidationRule constructor.
     *
     * @param \ACP3\Modules\ACP3\Permissions\Model\Repository\AclPrivilegesRepository $privilegeRepository
     */
    public function __construct(AclPrivilegesRepository $privilegeRepository)
    {
        $this->privilegeRepository = $privilegeRepository;
    }

    /**
     * @inheritdoc
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        if (\is_array($data) && \array_key_exists($field, $data)) {
            return $this->isValid($data[$field], $field, $extra);
        }

        return !empty($data) && \is_array($data) ? $this->privilegesExist($data) : false;
    }

    /**
     * Überprüft, ob die übergebenen Privilegien existieren und
     * plausible Werte enthalten
     *
     * @param array $privilegeIds
     *
     * @return boolean
     */
    public function privilegesExist(array $privilegeIds)
    {
        $valid = false;
        foreach ($this->privilegeRepository->getAllPrivileges() as $privilege) {
            $valid = false;
            foreach ($privilegeIds as $module) {
                foreach ($module as $privilegeId => $permission) {
                    if ($this->isValidPrivilege($privilegeId, $privilege, $permission)) {
                        $valid = true;

                        break 2;
                    }
                }
            }
        }

        return $valid;
    }

    /**
     * @param int   $privilegeId
     * @param array $privilege
     * @param int   $permission
     *
     * @return bool
     */
    protected function isValidPrivilege($privilegeId, array $privilege, $permission)
    {
        return $privilegeId == $privilege['id']
        && $permission >= PermissionEnum::DENY_ACCESS
        && $permission <= PermissionEnum::INHERIT_ACCESS;
    }
}
