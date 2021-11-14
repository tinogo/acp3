<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\View\Renderer\Smarty\Functions;

use ACP3\Core\Date;

class DateFormat extends AbstractFunction
{
    public function __construct(protected Date $date)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $params, \Smarty_Internal_Template $smarty)
    {
        $format = $params['format'] ?? 'long';

        if (isset($params['date'])) {
            return $this->date->format($params['date'], $format);
        }

        return '';
    }
}
