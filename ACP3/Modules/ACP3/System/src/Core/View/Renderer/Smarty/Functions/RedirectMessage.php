<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\System\Core\View\Renderer\Smarty\Functions;

use ACP3\Core;
use ACP3\Core\View\Renderer\Smarty\Functions\AbstractFunction;

class RedirectMessage extends AbstractFunction
{
    public function __construct(private Core\Helpers\RedirectMessages $redirectMessages)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function __invoke(array $params, \Smarty_Internal_Template $smarty)
    {
        $smarty->smarty->assign('redirect', $this->redirectMessages->getMessage());

        return $smarty->smarty->fetch('asset:System/Partials/redirect_message.tpl');
    }
}
