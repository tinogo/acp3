<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Core\Cache;

use ACP3\Core\Environment\ApplicationMode;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\System\Installer\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CacheResponseTrait
 * @package ACP3\Core\Cache
 */
trait CacheResponseTrait
{
    /**
     * @return Response
     */
    abstract protected function getResponse();

    /**
     * @return string
     */
    abstract protected function getApplicationMode();

    /**
     * @return SettingsInterface
     */
    abstract protected function getSettings();

    /**
     * @param int $lifetime Cache TTL in seconds
     */
    public function setCacheResponseCacheable($lifetime = 60)
    {
        $response = $this->getResponse();

        if ($this->disallowPageCache()) {
            $response->setPrivate();
            $lifetime = null;
        } else {
            $response->setPublic();
        }

        $response
            ->setVary('X-User-Context-Hash')
            ->setMaxAge($lifetime)
            ->setSharedMaxAge($lifetime);
    }

    /**
     * @return bool
     */
    protected function disallowPageCache()
    {
        $systemSettings = $this->getSettings()->getSettings(Schema::MODULE_NAME);

        return $this->getApplicationMode() === ApplicationMode::DEVELOPMENT
        || $systemSettings['page_cache_is_enabled'] == 0;
    }
}
