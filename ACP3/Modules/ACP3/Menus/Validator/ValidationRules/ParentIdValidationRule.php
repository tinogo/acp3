<?php
namespace ACP3\Modules\ACP3\Menus\Validator\ValidationRules;

use ACP3\Core\Validator\ValidationRules\AbstractValidationRule;
use ACP3\Modules\ACP3\Menus\Model\MenuItemRepository;

/**
 * Class ParentIdValidationRule
 * @package ACP3\Modules\ACP3\Menus\Validator\ValidationRules
 */
class ParentIdValidationRule extends AbstractValidationRule
{
    const NAME = 'menus_menu_item_parent_id';

    /**
     * @var \ACP3\Modules\ACP3\Menus\Model\MenuItemRepository
     */
    protected $menuItemRepository;

    /**
     * ParentIdValidationRule constructor.
     *
     * @param \ACP3\Modules\ACP3\Menus\Model\MenuItemRepository $menuItemRepository
     */
    public function __construct(MenuItemRepository $menuItemRepository)
    {
        $this->menuItemRepository = $menuItemRepository;
    }

    /**
     * @inheritdoc
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        if (is_array($data) && array_key_exists($field, $data)) {
            return $this->isValid($data[$field], $field, $extra);
        }

        return $this->checkParentIdExists($field);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    protected function checkParentIdExists($value)
    {
        return !empty($value) && $this->menuItemRepository->menuItemExists($value);
    }
}