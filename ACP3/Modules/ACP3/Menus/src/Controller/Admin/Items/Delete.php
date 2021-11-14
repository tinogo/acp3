<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Controller\Admin\Items;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Menus;

class Delete extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        private FormAction $actionHelper,
        private Menus\Model\MenuItemsModel $menuItemsModel
    ) {
        parent::__construct($context);
    }

    public function __invoke(?string $action = null): array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->actionHelper->handleDeleteAction(
            $action,
            fn (array $items) => $this->menuItemsModel->delete($items),
            null,
            'acp/menus'
        );
    }
}
