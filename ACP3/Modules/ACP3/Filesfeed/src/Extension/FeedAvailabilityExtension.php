<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Filesfeed\Extension;

use ACP3\Core\Date;
use ACP3\Core\Helpers\StringFormatter;
use ACP3\Core\Router\RouterInterface;
use ACP3\Modules\ACP3\Feeds\Extension\FeedAvailabilityExtensionInterface;
use ACP3\Modules\ACP3\Files\Installer\Schema;
use ACP3\Modules\ACP3\Files\Repository\FilesRepository;

class FeedAvailabilityExtension implements FeedAvailabilityExtensionInterface
{
    public function __construct(private Date $date, private RouterInterface $router, private StringFormatter $formatter, private FilesRepository $filesRepository)
    {
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return Schema::MODULE_NAME;
    }

    /**
     * @return array
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function fetchFeedItems()
    {
        $items = [];
        $results = $this->filesRepository->getAll($this->date->getCurrentDateTime(), 10);
        $cResults = \count($results);

        for ($i = 0; $i < $cResults; ++$i) {
            $items[] = [
                'title' => $results[$i]['title'],
                'date' => $this->date->timestamp($results[$i]['start']),
                'description' => $this->formatter->shortenEntry($results[$i]['text'], 300, 0),
                'link' => $this->router->route('files/index/details/id_' . $results[$i]['id'], true),
            ];
        }

        return $items;
    }
}
