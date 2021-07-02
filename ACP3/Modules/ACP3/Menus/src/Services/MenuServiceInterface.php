<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Services;

interface MenuServiceInterface
{
    public function getAllMenuItems(): array;

    public function getVisibleMenuItemsByMenu(string $menuIdentifier): array;
}
