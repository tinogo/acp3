<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Articles\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\Controller\Context\WidgetContext;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Articles;

class CreatePost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        WidgetContext $context,
        private FormAction $actionHelper,
        private UserModelInterface $user,
        private Articles\Model\ArticlesModel $articlesModel,
        private Articles\Validation\AdminFormValidation $adminFormValidation
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(): array|string|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        return $this->actionHelper->handleSaveAction(function () {
            $formData = $this->request->getPost()->all();
            $this->adminFormValidation->validate($formData);

            $formData['user_id'] = $this->user->getUserId();

            return $this->articlesModel->save($formData);
        });
    }
}
