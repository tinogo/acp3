<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Permissions\Controller\Admin\Resources;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Permissions;

class EditPost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\WidgetContext         $context,
        private FormAction                                    $actionHelper,
        private Core\Modules                                  $modules,
        private Permissions\Model\AclResourceModel            $resourcesModel,
        private Permissions\Validation\ResourceFormValidation $resourceFormValidation
    ) {
        parent::__construct($context);
    }

    /**
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): array|string|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        return $this->actionHelper->handleSaveAction(function () use ($id) {
            $formData = $this->request->getPost()->all();

            $this->resourceFormValidation->validate($formData);

            $formData['module_id'] = $this->modules->getModuleInfo($formData['modules'])['id'] ?? 0;

            return $this->resourcesModel->save($formData, $id);
        });
    }
}
