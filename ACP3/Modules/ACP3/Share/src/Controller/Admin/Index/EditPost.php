<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Share\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Share\Model\ShareModel;
use ACP3\Modules\ACP3\Share\Validation\AdminFormValidation;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class EditPost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly FormAction $actionHelper,
        private readonly ShareModel $shareModel,
        private readonly AdminFormValidation $adminFormValidation
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|string|Response
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function __invoke(int $id): array|string|Response
    {
        return $this->actionHelper->handleSaveAction(function () use ($id) {
            $formData = $this->request->getPost()->all();

            $shareInfo = $this->shareModel->getOneById($id);

            $this->adminFormValidation
                ->withUriAlias($shareInfo['uri'])
                ->validate($formData);

            return $this->shareModel->save($formData, $id);
        });
    }
}
