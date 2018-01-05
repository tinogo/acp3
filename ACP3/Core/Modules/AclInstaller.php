<?php

namespace ACP3\Core\Modules;

use ACP3\Core\ACL\PermissionEnum;
use ACP3\Core\ACL\PrivilegeEnum;
use ACP3\Core\Cache;
use ACP3\Core\Controller\AreaEnum;
use ACP3\Core\Modules\Installer\SchemaInterface;
use ACP3\Modules\ACP3\Permissions;

class AclInstaller implements InstallerInterface
{
    const INSTALL_RESOURCES_AND_RULES = 1;
    const INSTALL_RESOURCES = 2;

    /**
     * @var \ACP3\Core\Cache
     */
    private $aclCache;
    /**
     * @var \ACP3\Core\Modules\SchemaHelper
     */
    private $schemaHelper;
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Model\Repository\RoleRepository
     */
    private $roleRepository;
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Model\Repository\PrivilegeRepository
     */
    private $privilegeRepository;
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Model\Repository\ResourceRepository
     */
    private $resourceRepository;
    /**
     * @var \ACP3\Modules\ACP3\Permissions\Model\Repository\RuleRepository
     */
    private $ruleRepository;

    /**
     * @param \ACP3\Core\Cache $aclCache
     * @param \ACP3\Core\Modules\SchemaHelper $schemaHelper
     * @param \ACP3\Modules\ACP3\Permissions\Model\Repository\RoleRepository $roleRepository
     * @param \ACP3\Modules\ACP3\Permissions\Model\Repository\RuleRepository $ruleRepository
     * @param \ACP3\Modules\ACP3\Permissions\Model\Repository\ResourceRepository $resourceRepository
     * @param \ACP3\Modules\ACP3\Permissions\Model\Repository\PrivilegeRepository $privilegeRepository
     */
    public function __construct(
        Cache $aclCache,
        SchemaHelper $schemaHelper,
        Permissions\Model\Repository\RoleRepository $roleRepository,
        Permissions\Model\Repository\RuleRepository $ruleRepository,
        Permissions\Model\Repository\ResourceRepository $resourceRepository,
        Permissions\Model\Repository\PrivilegeRepository $privilegeRepository
    ) {
        $this->aclCache = $aclCache;
        $this->schemaHelper = $schemaHelper;
        $this->roleRepository = $roleRepository;
        $this->ruleRepository = $ruleRepository;
        $this->resourceRepository = $resourceRepository;
        $this->privilegeRepository = $privilegeRepository;
    }

    /**
     * Fügt die zu einen Modul zugehörigen Ressourcen ein
     *
     * @param \ACP3\Core\Modules\Installer\SchemaInterface $schema
     * @param int $mode
     *
     * @return bool
     */
    public function install(SchemaInterface $schema, $mode = self::INSTALL_RESOURCES_AND_RULES)
    {
        $this->insertAclResources($schema);

        if ($mode === self::INSTALL_RESOURCES_AND_RULES) {
            $this->insertAclRules($schema->getModuleName());
        }

        $this->aclCache->getDriver()->deleteAll();

        return true;
    }

    /**
     * Inserts a new resource into the database
     *
     * @param SchemaInterface $schema
     */
    private function insertAclResources(SchemaInterface $schema)
    {
        foreach ($schema->specialResources() as $area => $controllers) {
            foreach ($controllers as $controller => $actions) {
                foreach ($actions as $action => $privilegeId) {
                    $insertValues = [
                        'module_id' => $this->schemaHelper->getModuleId($schema->getModuleName()),
                        'area' => !empty($area) ? strtolower($area) : AreaEnum::AREA_FRONTEND,
                        'controller' => strtolower($controller),
                        'page' => $this->convertCamelCaseToUnderscore($action),
                        'params' => '',
                        'privilege_id' => (int)$privilegeId
                    ];
                    $this->resourceRepository->insert($insertValues);
                }
            }
        }
    }

    /**
     * @param string $action
     *
     * @return string
     */
    private function convertCamelCaseToUnderscore($action)
    {
        return strtolower(preg_replace('/\B([A-Z])/', '_$1', $action));
    }

    /**
     * Insert new acl user rules
     *
     * @param string $moduleName
     */
    private function insertAclRules($moduleName)
    {
        $roles = $this->roleRepository->getAllRoles();
        $privileges = $this->privilegeRepository->getAllPrivilegeIds();
        $moduleId = $this->schemaHelper->getModuleId($moduleName);

        foreach ($roles as $role) {
            foreach ($privileges as $privilege) {
                $insertValues = [
                    'id' => '',
                    'role_id' => $role['id'],
                    'module_id' => $moduleId,
                    'privilege_id' => $privilege['id'],
                    'permission' => $this->getDefaultAclRulePermission($role, $privilege)
                ];
                $this->ruleRepository->insert($insertValues);
            }
        }
    }

    /**
     * @param array $role
     * @param array $privilege
     *
     * @return int
     */
    private function getDefaultAclRulePermission($role, $privilege)
    {
        $permission = PermissionEnum::DENY_ACCESS;
        if ($role['id'] == 1 &&
            ($privilege['id'] == PrivilegeEnum::FRONTEND_VIEW || $privilege['id'] == PrivilegeEnum::FRONTEND_CREATE)
        ) {
            $permission = PermissionEnum::PERMIT_ACCESS;
        }
        if ($role['id'] > 1 && $role['id'] < 4) {
            $permission = PermissionEnum::INHERIT_ACCESS;
        }
        if ($role['id'] == 3 && $privilege['id'] == PrivilegeEnum::ADMIN_VIEW) {
            $permission = PermissionEnum::PERMIT_ACCESS;
        }
        if ($role['id'] == 4) {
            $permission = PermissionEnum::PERMIT_ACCESS;
        }

        return $permission;
    }

    /**
     * Löscht die zu einem Modul zugehörigen Ressourcen
     *
     * @param \ACP3\Core\Modules\Installer\SchemaInterface $schema
     *
     * @return bool
     */
    public function uninstall(SchemaInterface $schema)
    {
        $this->aclCache->getDriver()->deleteAll();

        return true;
    }
}
