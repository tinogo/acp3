<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Validation\ValidationRules;

class AccountExistsByNameValidationRule extends AccountNotExistsByNameValidationRule
{
    /**
     * {@inheritdoc}
     */
    protected function accountExists(string $data, int $userId): bool
    {
        return !parent::accountExists($data, $userId);
    }
}
