<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Categories\Validation;

use ACP3\Core;
use ACP3\Modules\ACP3\Categories\Installer\Schema;
use ACP3\Modules\ACP3\Categories\Model\Repository\CategoriesRepository;
use ACP3\Modules\ACP3\Categories\Validation\ValidationRules\AllowedSuperiorCategoryValidationRule;
use ACP3\Modules\ACP3\Categories\Validation\ValidationRules\DuplicateCategoryValidationRule;
use ACP3\Modules\ACP3\Categories\Validation\ValidationRules\ParentIdValidationRule;

class AdminFormValidation extends Core\Validation\AbstractFormValidation
{
    /**
     * @var \ACP3\Modules\ACP3\Categories\Model\Repository\CategoriesRepository
     */
    protected $categoryRepository;
    /**
     * @var Core\Settings\SettingsInterface
     */
    private $settings;
    /**
     * @var array
     */
    protected $file = [];
    /**
     * @var int|null
     */
    protected $categoryId;

    /**
     * Validator constructor.
     *
     * @param \ACP3\Core\I18n\TranslatorInterface $translator
     * @param \ACP3\Core\Validation\Validator $validator
     * @param Core\Settings\SettingsInterface $settings
     * @param \ACP3\Modules\ACP3\Categories\Model\Repository\CategoriesRepository $categoryRepository
     */
    public function __construct(
        Core\I18n\TranslatorInterface $translator,
        Core\Validation\Validator $validator,
        Core\Settings\SettingsInterface $settings,
        CategoriesRepository $categoryRepository
    ) {
        parent::__construct($translator, $validator);

        $this->categoryRepository = $categoryRepository;
        $this->settings = $settings;
    }

    /**
     * @param array $file
     *
     * @return AdminFormValidation
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @param int $categoryId
     *
     * @return AdminFormValidation
     */
    public function setCategoryId(?int $categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $formData)
    {
        $settings = $this->settings->getSettings(Schema::MODULE_NAME);

        $this->validator
            ->addConstraint(Core\Validation\ValidationRules\FormTokenValidationRule::class)
            ->addConstraint(
                Core\Validation\ValidationRules\NotEmptyValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'title',
                    'message' => $this->translator->t('categories', 'title_to_short'),
                ]
            )
            ->addConstraint(
                Core\Validation\ValidationRules\PictureValidationRule::class,
                [
                    'data' => $this->file,
                    'field' => 'picture',
                    'message' => $this->translator->t('categories', 'invalid_image_selected'),
                    'extra' => [
                        'width' => $settings['width'],
                        'height' => $settings['height'],
                        'filesize' => $settings['filesize'],
                        'required' => false,
                    ],
                ]
            )
            ->addConstraint(
                DuplicateCategoryValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'title',
                    'message' => $this->translator->t('categories', 'category_already_exists'),
                    'extra' => [
                        'module_id' => empty($this->categoryId) ? $formData['module_id'] : $this->categoryRepository->getModuleIdByCategoryId($this->categoryId),
                        'category_id' => $this->categoryId,
                    ],
                ]
            )
            ->addConstraint(
                ParentIdValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'parent_id',
                    'message' => $this->translator->t('categories', 'select_superior_category'),
                ]
            )
            ->addConstraint(
                AllowedSuperiorCategoryValidationRule::class,
                [
                    'data' => $formData,
                    'field' => ['parent_id', 'module_id'],
                    'message' => $this->translator->t('categories', 'superior_category_not_allowed'),
                ]
            );

        if (empty($this->categoryId)) {
            $this->validator->addConstraint(
                Core\Validation\ValidationRules\NotEmptyValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'module_id',
                    'message' => $this->translator->t('categories', 'select_module'),
                ]
            );
        }

        $this->validator->validate();
    }
}
