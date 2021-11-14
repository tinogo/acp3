<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Categories\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Categories;

class Index extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        private Categories\ViewProviders\DataGridViewProvider $dataGridViewProvider
    ) {
        parent::__construct($context);
    }

    public function __invoke()
    {
        return ($this->dataGridViewProvider)();
    }
}
