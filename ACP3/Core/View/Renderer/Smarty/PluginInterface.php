<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\View\Renderer\Smarty;

interface PluginInterface
{
    /**
     * @return string
     */
    public function getExtensionName();

    /**
     * @param \Smarty $smarty
     */
    public function register(\Smarty $smarty);
}
