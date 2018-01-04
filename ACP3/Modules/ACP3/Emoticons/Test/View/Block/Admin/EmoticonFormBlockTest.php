<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Emoticons\Test\View\Block\Admin;

use ACP3\Core\Test\View\Block\AbstractFormBlockTest;
use ACP3\Core\View\Block\BlockInterface;
use ACP3\Modules\ACP3\Emoticons\View\Block\Admin\EmoticonManageFormBlock;

class EmoticonFormBlockTest extends AbstractFormBlockTest
{
    /**
     * @inheritdoc
     */
    protected function instantiateBlock(): BlockInterface
    {
        return new EmoticonManageFormBlock($this->context);
    }

    /**
     * @inheritdoc
     */
    protected function getExpectedArrayKeys(): array
    {
        return [
            'form',
            'form_token'
        ];
    }
}
