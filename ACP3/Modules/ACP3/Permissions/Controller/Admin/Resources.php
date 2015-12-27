<?php

namespace ACP3\Modules\ACP3\Permissions\Controller\Admin;

use ACP3\Core;
use ACP3\Modules\ACP3\Permissions;

/**
 * Class Resources
 * @package ACP3\Modules\ACP3\Permissions\Controller\Admin
 */
class Resources extends Core\Modules\AdminController
{
    /**
     * @var \ACP3\Core\Helpers\FormToken
     */
    protected $formTokenHelper;
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Model\ResourceRepository
     */
    protected $resourceRepository;
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Cache
     */
    protected $permissionsCache;
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Validation\ResourceFormValidation
     */
    protected $resourceFormValidation;

    /**
     * @param \ACP3\Core\Modules\Controller\AdminContext                       $context
     * @param \ACP3\Core\Helpers\FormToken                                     $formTokenHelper
     * @param \ACP3\Modules\ACP3\Permissions\Model\ResourceRepository          $resourceRepository
     * @param \ACP3\Modules\ACP3\Permissions\Cache                             $permissionsCache
     * @param \ACP3\Modules\ACP3\Permissions\Validation\ResourceFormValidation $resourceFormValidation
     */
    public function __construct(
        Core\Modules\Controller\AdminContext $context,
        Core\Helpers\FormToken $formTokenHelper,
        Permissions\Model\ResourceRepository $resourceRepository,
        Permissions\Cache $permissionsCache,
        Permissions\Validation\ResourceFormValidation $resourceFormValidation
    )
    {
        parent::__construct($context);

        $this->formTokenHelper = $formTokenHelper;
        $this->resourceRepository = $resourceRepository;
        $this->permissionsCache = $permissionsCache;
        $this->resourceFormValidation = $resourceFormValidation;
    }

    /**
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function actionCreate()
    {
        if ($this->request->getPost()->isEmpty() === false) {
            return $this->_createPost($this->request->getPost()->all());
        }

        $modules = $this->modules->getActiveModules();
        foreach ($modules as $row) {
            $modules[$row['name']]['selected'] = $this->get('core.helpers.forms')->selectEntry('modules', $row['name']);
        }

        $privileges = $this->acl->getAllPrivileges();
        $c_privileges = count($privileges);
        for ($i = 0; $i < $c_privileges; ++$i) {
            $privileges[$i]['selected'] = $this->get('core.helpers.forms')->selectEntry('privileges', $privileges[$i]['id']);
        }

        $this->formTokenHelper->generateFormToken();

        return [
            'modules' => $modules,
            'privileges' => $privileges,
            'form' => array_merge(['resource' => '', 'area' => '', 'controller' => ''], $this->request->getPost()->all())
        ];
    }

    /**
     * @param string $action
     *
     * @return mixed
     * @throws \ACP3\Core\Exceptions\ResultNotExists
     */
    public function actionDelete($action = '')
    {
        return $this->actionHelper->handleDeleteAction(
            $this,
            $action,
            function ($items) {
                $bool = false;

                foreach ($items as $item) {
                    $bool = $this->resourceRepository->delete($item);
                }

                $this->permissionsCache->saveResourcesCache();

                return $bool;
            }
        );
    }

    /**
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ACP3\Core\Exceptions\ResultNotExists
     */
    public function actionEdit($id)
    {
        $resource = $this->resourceRepository->getResourceById($id);
        if (!empty($resource)) {
            if ($this->request->getPost()->isEmpty() === false) {
                return $this->_editPost($this->request->getPost()->all(), $id);
            }

            $privileges = $this->acl->getAllPrivileges();
            $c_privileges = count($privileges);
            for ($i = 0; $i < $c_privileges; ++$i) {
                $privileges[$i]['selected'] = $this->get('core.helpers.forms')->selectEntry('privileges', $privileges[$i]['id'], $resource['privilege_id']);
            }

            $defaults = [
                'resource' => $resource['page'],
                'area' => $resource['area'],
                'controller' => $resource['controller'],
                'modules' => $resource['module_name']
            ];

            $this->formTokenHelper->generateFormToken();

            return [
                'privileges' => $privileges,
                'form' => array_merge($defaults, $this->request->getPost()->all())
            ];
        }

        throw new Core\Exceptions\ResultNotExists();
    }

    /**
     * @return array
     */
    public function actionIndex()
    {
        $resources = $this->resourceRepository->getAllResources();
        $c_resources = count($resources);
        $output = [];
        for ($i = 0; $i < $c_resources; ++$i) {
            if ($this->modules->isActive($resources[$i]['module_name']) === true) {
                $module = $this->translator->t($resources[$i]['module_name'], $resources[$i]['module_name']);
                $output[$module][] = $resources[$i];
            }
        }
        ksort($output);

        return [
            'resources' => $output,
            'can_delete_resource' => $this->acl->hasPermission('admin/permissions/resources/delete'),
            'can_edit_resource' => $this->acl->hasPermission('admin/permissions/resources/edit')
        ];
    }

    /**
     * @param array $formData
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function _createPost(array $formData)
    {
        return $this->actionHelper->handleCreatePostAction(function () use ($formData) {
            $this->resourceFormValidation->validate($formData);

            $moduleInfo = $this->modules->getModuleInfo($formData['modules']);
            $insertValues = [
                'id' => '',
                'module_id' => $moduleInfo['id'],
                'area' => $formData['area'],
                'controller' => $formData['controller'],
                'page' => $formData['resource'],
                'params' => '',
                'privilege_id' => $formData['privileges'],
            ];
            $bool = $this->resourceRepository->insert($insertValues);

            $this->permissionsCache->saveResourcesCache();

            $this->formTokenHelper->unsetFormToken();

            return $bool;
        });
    }

    /**
     * @param array $formData
     * @param int   $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function _editPost(array $formData, $id)
    {
        return $this->actionHelper->handleEditPostAction(function () use ($formData, $id) {
            $this->resourceFormValidation->validate($formData);

            $updateValues = [
                'controller' => $formData['controller'],
                'area' => $formData['area'],
                'page' => $formData['resource'],
                'privilege_id' => $formData['privileges'],
            ];
            $bool = $this->resourceRepository->update($updateValues, $id);

            $this->permissionsCache->saveResourcesCache();

            $this->formTokenHelper->unsetFormToken();

            return $bool;
        });
    }
}
