<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\DataGrid\ColumnRenderer\Event;

use ACP3\Core\DataGrid\ColumnRenderer\OptionColumnRenderer\OptionRenderer;
use Symfony\Contracts\EventDispatcher\Event;

class CustomOptionEvent extends Event
{
    /**
     * @var array
     */
    private $dbResultRow;

    public function __construct(private OptionRenderer $optionRenderer, array $dbResultRow, private string $identifier)
    {
        $this->dbResultRow = $dbResultRow;
    }

    public function getOptionRenderer(): OptionRenderer
    {
        return $this->optionRenderer;
    }

    public function getDbResultRow(): array
    {
        return $this->dbResultRow;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
