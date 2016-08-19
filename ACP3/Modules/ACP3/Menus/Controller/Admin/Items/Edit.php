<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers. See the LICENCE file at the top-level module directory for licencing
 * details.
 */

namespace ACP3\Modules\ACP3\Menus\Controller\Admin\Items;

use ACP3\Core;
use ACP3\Modules\ACP3\Menus;

/**
 * Class Edit
 * @package ACP3\Modules\ACP3\Menus\Controller\Admin\Items
 */
class Edit extends AbstractFormAction
{
    /**
     * @var \ACP3\Core\NestedSet\NestedSet
     */
    protected $nestedSet;
    /**
     * @var \ACP3\Core\Helpers\FormToken
     */
    protected $formTokenHelper;
    /**
     * @var \ACP3\Modules\ACP3\Menus\Cache
     */
    protected $menusCache;
    /**
     * @var \ACP3\Modules\ACP3\Menus\Validation\MenuItemFormValidation
     */
    protected $menuItemFormValidation;
    /**
     * @var \ACP3\Modules\ACP3\Menus\Model\Repository\MenuItemRepository
     */
    protected $menuItemRepository;
    /**
     * @var \ACP3\Modules\ACP3\Menus\Helpers\MenuItemFormFields
     */
    protected $menuItemFormFieldsHelper;

    /**
     * Edit constructor.
     *
     * @param \ACP3\Core\Controller\Context\AdminContext                 $context
     * @param \ACP3\Core\NestedSet\NestedSet                                       $nestedSet
     * @param \ACP3\Core\Helpers\Forms                                   $formsHelper
     * @param \ACP3\Core\Helpers\FormToken                               $formTokenHelper
     * @param \ACP3\Modules\ACP3\Menus\Model\Repository\MenuItemRepository          $menuItemRepository
     * @param \ACP3\Modules\ACP3\Menus\Cache                             $menusCache
     * @param \ACP3\Modules\ACP3\Menus\Helpers\MenuItemFormFields        $menuItemFormFieldsHelper
     * @param \ACP3\Modules\ACP3\Menus\Validation\MenuItemFormValidation $menuItemFormValidation
     */
    public function __construct(
        Core\Controller\Context\AdminContext $context,
        Core\NestedSet\NestedSet $nestedSet,
        Core\Helpers\Forms $formsHelper,
        Core\Helpers\FormToken $formTokenHelper,
        Menus\Model\Repository\MenuItemRepository $menuItemRepository,
        Menus\Cache $menusCache,
        Menus\Helpers\MenuItemFormFields $menuItemFormFieldsHelper,
        Menus\Validation\MenuItemFormValidation $menuItemFormValidation
    ) {
        parent::__construct($context, $formsHelper);

        $this->nestedSet = $nestedSet;
        $this->formTokenHelper = $formTokenHelper;
        $this->menuItemRepository = $menuItemRepository;
        $this->menusCache = $menusCache;
        $this->menuItemFormFieldsHelper = $menuItemFormFieldsHelper;
        $this->menuItemFormValidation = $menuItemFormValidation;
    }

    /**
     * @param int $id
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    public function execute($id)
    {
        $menuItem = $this->menuItemRepository->getOneById($id);

        if (empty($menuItem) === false) {
            $this->title->setPageTitlePostfix($menuItem['title']);

            if ($this->request->getPost()->count() !== 0) {
                return $this->executePost($this->request->getPost()->all(), $menuItem, $id);
            }

            if ($this->articlesHelpers) {
                $matches = [];
                if (count($this->request->getPost()->all()) == 0 && $menuItem['mode'] == 4) {
                    preg_match_all(Menus\Helpers\MenuItemsList::ARTICLES_URL_KEY_REGEX, $menuItem['uri'], $matches);
                }

                $this->view->assign('articles',
                    $this->articlesHelpers->articlesList(!empty($matches[2]) ? $matches[2][0] : '')
                );
            }

            $this->view->assign(
                $this->menuItemFormFieldsHelper->createMenuItemFormFields(
                    $menuItem['block_id'],
                    $menuItem['parent_id'],
                    $menuItem['left_id'],
                    $menuItem['right_id'],
                    $menuItem['display']
                )
            );

            return [
                'mode' => $this->fetchMenuItemTypes($menuItem['mode']),
                'modules' => $this->fetchModules($menuItem),
                'target' => $this->formsHelper->linkTargetChoicesGenerator('target', $menuItem['target']),
                'form' => array_merge($menuItem, $this->request->getPost()->all()),
                'form_token' => $this->formTokenHelper->renderFormToken()
            ];
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }

    /**
     * @param array $formData
     * @param array $menuItem
     * @param int   $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function executePost(array $formData, array $menuItem, $id)
    {
        return $this->actionHelper->handlePostAction(
            function () use ($formData, $menuItem, $id) {
                $this->menuItemFormValidation->validate($formData);

                $updateValues = [
                    'mode' => $this->fetchMenuItemModeForSave($formData),
                    'block_id' => $formData['block_id'],
                    'parent_id' => $formData['parent_id'],
                    'display' => $formData['display'],
                    'title' => $this->get('core.helpers.secure')->strEncode($formData['title']),
                    'uri' => $this->fetchMenuItemUriForSave($formData),
                    'target' => $formData['display'] == 0 ? 1 : $formData['target'],
                ];

                $result = $this->nestedSet->editNode(
                    $id,
                    (int)$formData['parent_id'],
                    (int)$formData['block_id'],
                    $updateValues,
                    Menus\Model\Repository\MenuItemRepository::TABLE_NAME,
                    true
                );

                $this->menusCache->saveMenusCache();

                Core\Cache\Purge::doPurge($this->appPath->getCacheDir() . 'http');

                return $this->redirectMessages()->setMessage(
                    $result,
                    $this->translator->t('system', $result !== false ? 'edit_success' : 'edit_error'),
                    'acp/menus'
                );
            },
            'acp/menus'
        );
    }
}
