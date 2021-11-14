<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Admin\Index;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\WidgetContext;
use ACP3\Core\Helpers\FormAction;
use ACP3\Core\Helpers\Secure;
use ACP3\Modules\ACP3\Comments;
use ACP3\Modules\ACP3\Comments\Validation\AdminSettingsFormValidation;

class SettingsPost extends AbstractWidgetAction
{
    public function __construct(
        WidgetContext $context,
        private FormAction $actionHelper,
        private Secure $secureHelper,
        private AdminSettingsFormValidation $adminSettingsFormValidation
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(): array|string|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->actionHelper->handleSettingsPostAction(function () {
            $formData = $this->request->getPost()->all();
            $this->adminSettingsFormValidation->validate($formData);

            $data = [
                'dateformat' => $this->secureHelper->strEncode($formData['dateformat']),
            ];

            return $this->config->saveSettings($data, Comments\Installer\Schema::MODULE_NAME);
        });
    }
}
