<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Cookieconsent\Controller\Admin\Index;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\WidgetContext;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Cookieconsent\Installer\Schema;
use ACP3\Modules\ACP3\Cookieconsent\Validation\AdminSettingsFormValidation;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SettingsPost extends AbstractWidgetAction
{
    public function __construct(
        WidgetContext $context,
        private FormAction $actionHelper,
        private AdminSettingsFormValidation $cookieConsentValidator
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|string|JsonResponse|RedirectResponse
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function __invoke(): array|string|JsonResponse|RedirectResponse
    {
        return $this->actionHelper->handleSettingsPostAction(
            function () {
                $formData = $this->request->getPost()->all();

                $this->cookieConsentValidator->validate($formData);

                $data = [
                    'enabled' => (int) $formData['enabled'],
                ];

                return $this->config->saveSettings($data, Schema::MODULE_NAME);
            }
        );
    }
}
