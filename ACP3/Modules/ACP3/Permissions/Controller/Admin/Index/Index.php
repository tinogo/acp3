<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Permissions\Controller\Admin\Index;

use ACP3\Core;

class Index extends Core\Controller\AbstractFrontendAction
{
    /**
     * @return array
     */
    public function execute()
    {
        return [
            'roles' => $this->fetchRoles(),
            'can_delete' => $this->acl->hasPermission('admin/permissions/index/delete'),
            'can_edit' => $this->acl->hasPermission('admin/permissions/index/edit'),
            'can_order' => $this->acl->hasPermission('admin/permissions/index/order'),
        ];
    }

    /**
     * @return array
     */
    private function fetchRoles(): array
    {
        $roles = $this->acl->getAllRoles();
        $cRoles = \count($roles);

        for ($i = 0; $i < $cRoles; ++$i) {
            $roles[$i]['spaces'] = \str_repeat('&nbsp;&nbsp;', $roles[$i]['level']);
        }

        return $roles;
    }
}
