<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Polls\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Polls;
use ACP3\Modules\ACP3\System\Installer\Schema;
use Symfony\Component\HttpFoundation\Response;

class Result extends Core\Controller\AbstractFrontendAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var Core\Date
     */
    private $date;
    /**
     * @var \ACP3\Modules\ACP3\Polls\Model\Repository\PollRepository
     */
    private $pollRepository;
    /**
     * @var \ACP3\Modules\ACP3\Polls\ViewProviders\PollResultViewProvider
     */
    private $pollResultViewProvider;

    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        Core\Date $date,
        Polls\Model\Repository\PollRepository $pollRepository,
        Polls\ViewProviders\PollResultViewProvider $pollResultViewProvider
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->pollRepository = $pollRepository;
        $this->pollResultViewProvider = $pollResultViewProvider;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(int $id): Response
    {
        if ($this->pollRepository->pollExists($id, $this->date->getCurrentDateTime()) === true) {
            $response = $this->renderTemplate(null, ($this->pollResultViewProvider)($id));
            $this->setCacheResponseCacheable($response, $this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            return $response;
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
