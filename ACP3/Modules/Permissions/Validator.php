<?php
/**
 * Created by PhpStorm.
 * User: goratsch
 * Date: 21.06.14
 * Time: 23:57
 */

namespace ACP3\Modules\Permissions;

use ACP3\Core;

/**
 * Class Validator
 * @package ACP3\Modules\Permissions
 */
class Validator extends Core\Validator\AbstractValidator
{
    /**
     * @var \ACP3\Core\URI
     */
    protected $uri;
    /**
     * @var Model
     */
    protected $permissionsModel;

    public function __construct(Core\Lang $lang, Core\URI $uri, Model $permissionsModel)
    {
        parent::__construct($lang);

        $this->uri = $uri;
        $this->permissionsModel = $permissionsModel;
    }

    /**
     * @param array $formData
     * @throws \ACP3\Core\Exceptions\ValidationFailed
     */
    public function validateCreate(array $formData)
    {
        $this->validateFormKey();

        $errors = array();
        if (empty($formData['name'])) {
            $errors['name'] = $this->lang->t('system', 'name_to_short');
        }
        if (!empty($formData['name']) && $this->permissionsModel->roleExistsByName($formData['name']) === true) {
            $errors['name'] = $this->lang->t('permissions', 'role_already_exists');
        }
        if (empty($formData['privileges']) || is_array($formData['privileges']) === false) {
            $errors[] = $this->lang->t('permissions', 'no_privilege_selected');
        }
        if (!empty($formData['privileges']) && Core\Validate::aclPrivilegesExist($formData['privileges']) === false) {
            $errors[] = $this->lang->t('permissions', 'invalid_privileges');
        }

        if (!empty($errors)) {
            throw new Core\Exceptions\ValidationFailed(Core\Functions::errorBox($errors));
        }
    }

    /**
     * @param array $formData
     * @throws \ACP3\Core\Exceptions\ValidationFailed
     */
    public function validateCreateResource(array $formData)
    {
        $this->validateFormKey();

        $errors = array();
        if (empty($formData['modules']) || Core\Modules::isInstalled($formData['modules']) === false) {
            $errors['modules'] = $this->lang->t('permissions', 'select_module');
        }
        if (empty($formData['area']) || in_array($formData['area'], array('admin', 'frontend', 'sidebar')) === false) {
            $errors['controller'] = $this->lang->t('permissions', 'type_in_area');
        }
        if (empty($formData['controller'])) {
            $errors['controller'] = $this->lang->t('permissions', 'type_in_controller');
        }
        if (empty($formData['resource']) || preg_match('=/=', $formData['resource']) || Core\Validate::isInternalURI(strtolower($formData['modules']) . '/' . $formData['controller'] . '/' . $formData['resource'] . '/') === false) {
            $errors['resource'] = $this->lang->t('permissions', 'type_in_resource');
        }
        if (empty($formData['privileges']) || Core\Validate::isNumber($formData['privileges']) === false) {
            $errors['privileges'] = $this->lang->t('permissions', 'select_privilege');
        }
        if (Core\Validate::isNumber($formData['privileges']) && $this->permissionsModel->resourceExists($formData['privileges']) === false) {
            $errors['privileges'] = $this->lang->t('permissions', 'privilege_does_not_exist');
        }

        if (!empty($errors)) {
            throw new Core\Exceptions\ValidationFailed(Core\Functions::errorBox($errors));
        }
    }

    /**
     * @param array $formData
     * @throws \ACP3\Core\Exceptions\ValidationFailed
     */
    public function validateEdit(array $formData)
    {
        $this->validateFormKey();

        $errors = array();
        if (empty($formData['name'])) {
            $errors['name'] = $this->lang->t('system', 'name_to_short');
        }
        if (!empty($formData['name']) && $this->permissionsModel->roleExistsByName($formData['name'], $this->uri->id) === true) {
            $errors['name'] = $this->lang->t('permissions', 'role_already_exists');
        }
        if (empty($formData['privileges']) || is_array($formData['privileges']) === false) {
            $errors[] = $this->lang->t('permissions', 'no_privilege_selected');
        }
        if (!empty($formData['privileges']) && Core\Validate::aclPrivilegesExist($formData['privileges']) === false) {
            $errors[] = $this->lang->t('permissions', 'invalid_privileges');
        }

        if (!empty($errors)) {
            throw new Core\Exceptions\ValidationFailed(Core\Functions::errorBox($errors));
        }
    }

    /**
     * @param array $formData
     * @throws \ACP3\Core\Exceptions\ValidationFailed
     */
    public function validateEditResource(array $formData)
    {
        $this->validateFormKey();

        $errors = array();
        if (empty($formData['modules']) || Core\Modules::isInstalled($formData['modules']) === false) {
            $errors['modules'] = $this->lang->t('permissions', 'select_module');
        }
        if (empty($formData['area']) || in_array($formData['area'], array('admin', 'frontend', 'sidebar')) === false) {
            $errors['controller'] = $this->lang->t('permissions', 'type_in_area');
        }
        if (empty($formData['controller'])) {
            $errors['controller'] = $this->lang->t('permissions', 'type_in_controller');
        }
        if (empty($formData['resource']) || preg_match('=/=', $formData['resource']) || Core\Validate::isInternalURI($formData['modules'] . '/' . $formData['controller'] . '/' . $formData['resource'] . '/') === false) {
            $errors['resource'] = $this->lang->t('permissions', 'type_in_resource');
        }
        if (empty($formData['privileges']) || Core\Validate::isNumber($formData['privileges']) === false) {
            $errors['privileges'] = $this->lang->t('permissions', 'select_privilege');
        }
        if (Core\Validate::isNumber($formData['privileges']) && $this->permissionsModel->resourceExists($formData['privileges']) === false) {
            $errors['privileges'] = $this->lang->t('permissions', 'privilege_does_not_exist');
        }

        if (!empty($errors)) {
            throw new Core\Exceptions\ValidationFailed(Core\Functions::errorBox($errors));
        }
    }

} 