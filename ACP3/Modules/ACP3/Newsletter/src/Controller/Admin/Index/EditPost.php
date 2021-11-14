<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Newsletter;

class EditPost extends AbstractFormAction
{
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        private FormAction $actionHelper,
        private UserModelInterface $user,
        private Newsletter\Model\NewsletterModel $newsletterModel,
        private Newsletter\Validation\AdminFormValidation $adminFormValidation,
        Newsletter\Helper\SendNewsletter $newsletterHelpers
    ) {
        parent::__construct($context, $newsletterHelpers);
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): array|string|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        return $this->actionHelper->handlePostAction(function () use ($id) {
            $formData = $this->request->getPost()->all();

            $settings = $this->config->getSettings(Newsletter\Installer\Schema::MODULE_NAME);

            $this->adminFormValidation->validate($formData);

            $formData['user_id'] = $this->user->getUserId();
            $result = $this->newsletterModel->save($formData, $id);

            [$text, $result] = $this->sendTestNewsletter(
                $formData['test'] == 1,
                $id,
                (bool) $result,
                $settings['mail']
            );

            return $this->actionHelper->setRedirectMessage($result, $text);
        });
    }
}
