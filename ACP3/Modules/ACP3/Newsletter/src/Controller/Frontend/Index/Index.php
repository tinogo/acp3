<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Newsletter;

class Index extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        private Newsletter\ViewProviders\NewsletterSubscribeViewProvider $newsletterSubscribeViewProvider
    ) {
        parent::__construct($context);
    }

    public function __invoke(): array
    {
        return ($this->newsletterSubscribeViewProvider)();
    }
}
