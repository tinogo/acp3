<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Controller\Admin\Index;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\WidgetContext;
use ACP3\Core\Helpers\RedirectMessages;
use ACP3\Modules\ACP3\Seo\Installer\Schema;
use ACP3\Modules\ACP3\Seo\Model\SitemapGenerationModel;

class Sitemap extends AbstractWidgetAction
{
    public function __construct(
        WidgetContext $context,
        private RedirectMessages $redirectMessages,
        private SitemapGenerationModel $sitemapGenerationModel
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \ACP3\Modules\ACP3\Seo\Exception\SitemapGenerationException
     */
    public function __invoke(): \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $result = false;
        $phrase = 'sitemap_error';
        if ($this->config->getSettings(Schema::MODULE_NAME)['sitemap_is_enabled'] == 1) {
            $result = $this->sitemapGenerationModel->save();
            $phrase = $result === true ? 'sitemap_success' : 'sitemap_error';
        }

        return $this->redirectMessages->setMessage($result, $this->translator->t('seo', $phrase));
    }
}
