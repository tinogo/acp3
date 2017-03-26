<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Seo\Event\Listener;

use ACP3\Core\ACL;
use ACP3\Core\Model\Event\ModelSaveEvent;
use ACP3\Modules\ACP3\Seo\Helper\UriAliasManager;
use ACP3\Modules\ACP3\Seo\Installer\Schema;

class InsertUriAliasOnModelAfterSaveListener
{
    /**
     * @var UriAliasManager
     */
    private $uriAliasManager;
    /**
     * @var ACL
     */
    private $acl;

    /**
     * InsertUriAliasOnModelAfterSaveListener constructor.
     * @param ACL $acl
     * @param UriAliasManager $uriAliasManager
     */
    public function __construct(
        ACL $acl,
        UriAliasManager $uriAliasManager)
    {
        $this->uriAliasManager = $uriAliasManager;
        $this->acl = $acl;
    }

    /**
     * @param ModelSaveEvent $event
     */
    public function insertUriAlias(ModelSaveEvent $event)
    {
        if ($this->acl->hasPermission('admin/seo/index/create')) {
            $formData = $event->getRawData();

            if ($event->getModuleName() !== Schema::MODULE_NAME && !empty($formData['seo_uri_pattern'])) {
                $this->uriAliasManager->insertUriAlias(
                    sprintf($formData['seo_uri_pattern'], $event->getEntryId()),
                    $formData['alias'],
                    $formData['seo_keywords'],
                    $formData['seo_description'],
                    (int)$formData['seo_robots']
                );
            }
        }
    }
}
