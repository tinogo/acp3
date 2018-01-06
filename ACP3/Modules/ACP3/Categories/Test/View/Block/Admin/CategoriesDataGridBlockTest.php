<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Categories\Test\View\Block\Admin;

use ACP3\Core\ACL\ACLInterface;
use ACP3\Core\Test\View\Block\AbstractDataGridBlockTest;
use ACP3\Core\View\Block\BlockInterface;
use ACP3\Modules\ACP3\Categories\View\Block\Admin\CategoriesDataGridBlock;

class CategoriesDataGridBlockTest extends AbstractDataGridBlockTest
{
    /**
     * @var ACLInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $acl;

    protected function setUpMockObjects()
    {
        parent::setUpMockObjects();

        $this->acl = $this->getMockBuilder(ACLInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasPermission'])
            ->getMockForAbstractClass();
    }

    /**
     * @inheritdoc
     */
    protected function instantiateBlock(): BlockInterface
    {
        return new CategoriesDataGridBlock($this->context, $this->acl);
    }

    /**
     * @inheritdoc
     */
    protected function getExpectedArrayKeys(): array
    {
        return [
            'grid',
            'show_mass_delete_button',
        ];
    }
}
