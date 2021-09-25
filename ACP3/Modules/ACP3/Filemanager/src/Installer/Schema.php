<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Filemanager\Installer;

use ACP3\Core\ACL\PrivilegeEnum;

class Schema implements \ACP3\Core\Installer\SchemaInterface
{
    public const MODULE_NAME = 'filemanager';

    public function specialResources(): array
    {
        return [
            'admin' => [
                'index' => [
                    'richfilemanager' => PrivilegeEnum::ADMIN_VIEW,
                ],
            ],
        ];
    }

    public function getModuleName(): string
    {
        return static::MODULE_NAME;
    }

    public function createTables(): array
    {
        return [];
    }

    public function removeTables(): array
    {
        return [];
    }

    public function settings(): array
    {
        return [];
    }
}
