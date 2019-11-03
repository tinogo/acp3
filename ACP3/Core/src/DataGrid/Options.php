<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\DataGrid;

class Options
{
    /**
     * @var string
     */
    private $identifier = '';
    /**
     * @var int
     */
    private $recordsPerPage = 20;
    /**
     * @var bool
     */
    private $enableMassAction = true;
    /**
     * @var bool
     */
    private $enableOptions = true;
    /**
     * @var bool
     */
    private $useAjax = false;
    /**
     * @var string
     */
    private $resourcePathEdit = '';
    /**
     * @var string
     */
    private $resourcePathDelete = '';

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return Options
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getRecordsPerPage(): int
    {
        return $this->recordsPerPage;
    }

    /**
     * @return Options
     */
    public function setRecordsPerPage(int $recordsPerPage): self
    {
        $this->recordsPerPage = $recordsPerPage;

        return $this;
    }

    public function isEnableOptions(): bool
    {
        return $this->enableOptions;
    }

    /**
     * @return Options
     */
    public function setEnableOptions(bool $enableOptions): self
    {
        $this->enableOptions = $enableOptions;

        return $this;
    }

    public function isUseAjax(): bool
    {
        return $this->useAjax;
    }

    /**
     * @return Options
     */
    public function setUseAjax(bool $useAjax): self
    {
        $this->useAjax = $useAjax;

        return $this;
    }

    public function getResourcePathEdit(): string
    {
        return $this->resourcePathEdit;
    }

    /**
     * @return Options
     */
    public function setResourcePathEdit(string $resourcePathEdit): self
    {
        $this->resourcePathEdit = $resourcePathEdit;

        return $this;
    }

    public function getResourcePathDelete(): string
    {
        return $this->resourcePathDelete;
    }

    /**
     * @return Options
     */
    public function setResourcePathDelete(string $resourcePathDelete): self
    {
        $this->resourcePathDelete = $resourcePathDelete;

        return $this;
    }

    public function isEnableMassAction(): bool
    {
        return $this->enableMassAction;
    }

    /**
     * @return Options
     */
    public function setEnableMassAction(bool $enableMassAction): self
    {
        $this->enableMassAction = $enableMassAction;

        return $this;
    }
}
