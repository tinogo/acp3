<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Filescomments\EventListener;

use ACP3\Core\Helpers\Forms;
use ACP3\Core\Modules;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Core\View;
use ACP3\Core\View\Event\TemplateEvent;
use ACP3\Modules\ACP3\Comments\Installer\Schema as CommentsSchema;
use ACP3\Modules\ACP3\Filescomments\Installer\Schema as FilesCommentsSchema;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OnFilesLayoutUpsertEventListener implements EventSubscriberInterface
{
    public function __construct(private Modules $modules, private View $view, private SettingsInterface $settings, private Forms $formsHelper)
    {
    }

    public function __invoke(TemplateEvent $event): void
    {
        if (!$this->modules->isInstalled(CommentsSchema::MODULE_NAME) || !$this->modules->isInstalled(FilesCommentsSchema::MODULE_NAME)) {
            return;
        }

        $settings = $this->settings->getSettings(FilesCommentsSchema::MODULE_NAME);

        if ($settings['comments'] == 1) {
            $formData = $event->getParameters()['form_data'];

            $this->view->assign(
                'comments',
                $this->formsHelper->yesNoCheckboxGenerator(
                    'comments',
                    $formData['comments'] ?? (int) $settings['comments']
                )
            );

            $event->addContent($this->view->fetchTemplate('Filescomments/Partials/files_layout_upsert.tpl'));
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'files.layout.upsert' => '__invoke',
        ];
    }
}
