<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Helpers;

class MenuConfiguration implements \Stringable
{
    public function __construct(private bool $useBootstrap = true, private string $selector = '', private string $dropdownItemSelector = '', private string $tag = 'ul', private string $itemTag = 'li', private string $itemSelectors = '', private string $dropdownWrapperTag = 'li', private string $linkSelector = '', private string $inlineStyle = '')
    {
    }

    public function isUseBootstrap(): bool
    {
        return $this->useBootstrap;
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function getDropdownItemSelector(): string
    {
        return $this->dropdownItemSelector;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getItemTag(): string
    {
        return $this->itemTag;
    }

    public function getItemSelectors(): string
    {
        return $this->itemSelectors;
    }

    public function getDropdownWrapperTag(): string
    {
        return $this->dropdownWrapperTag;
    }

    public function getLinkSelector(): string
    {
        return $this->linkSelector;
    }

    public function getInlineStyle(): string
    {
        return $this->inlineStyle;
    }

    public function __toString(): string
    {
        return implode(':', get_object_vars($this));
    }
}
