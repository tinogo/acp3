<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\View\Renderer\Smarty\Filters;

use ACP3\Core\Assets\Minifier\CSS;
use ACP3\Core\Assets\Minifier\DeferableCSS;

class MoveToHead extends AbstractMoveElementFilter
{
    public const ELEMENT_CATCHER_REGEX_PATTERN = '!@@@SMARTY:STYLESHEETS:BEGIN@@@(.*?)@@@SMARTY:STYLESHEETS:END@@@!is';
    protected const PLACEHOLDER = '<!-- STYLESHEETS -->';

    /**
     * @var \ACP3\Core\Assets\Minifier\MinifierInterface
     */
    private $cssMinifier;
    /**
     * @var \ACP3\Core\Assets\Minifier\DeferableCSS
     */
    private $deferableCssMinifier;

    public function __construct(CSS $cssMinifier, DeferableCSS $deferableCssMinifier)
    {
        $this->cssMinifier = $cssMinifier;
        $this->deferableCssMinifier = $deferableCssMinifier;
    }

    public function __invoke(string $tplOutput, \Smarty_Internal_Template $smarty): string
    {
        if (\strpos($tplOutput, static::PLACEHOLDER) !== false) {
            $tplOutput = \str_replace(
                static::PLACEHOLDER,
                $this->addElementFromMinifier() . $this->addElementsFromTemplates($tplOutput),
                $this->getCleanedUpTemplateOutput($tplOutput)
            );
        }

        return $tplOutput;
    }

    protected function addElementFromMinifier(): string
    {
        $deferableCssUri = $this->deferableCssMinifier->getURI();

        return '<link rel="stylesheet" type="text/css" href="' . $this->cssMinifier->getURI() . '">' . "\n"
            . '<link rel="stylesheet" href="' . $deferableCssUri . '" media="print" onload="this.media=\'all\'; this.onload=null;">' . "\n"
            . '<noscript><link rel="stylesheet" href="' . $deferableCssUri . '"></noscript>' . "\n";
    }
}
