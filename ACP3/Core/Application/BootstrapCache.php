<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Application;

use ACP3\Core\Application\BootstrapCache\Event\Listener\StaticAssetsListener;
use ACP3\Core\Application\BootstrapCache\Event\Listener\UserContextListener;
use ACP3\Core\Session\SessionHandlerInterface;
use FOS\HttpCache\SymfonyCache\CacheInvalidation;
use FOS\HttpCache\SymfonyCache\DebugListener;
use FOS\HttpCache\SymfonyCache\EventDispatchingHttpCache;
use FOS\HttpCache\SymfonyCache\PurgeListener;
use FOS\HttpCache\SymfonyCache\RefreshListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use Symfony\Component\HttpKernel\HttpCache\SurrogateInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class BootstrapCache extends HttpCache implements CacheInvalidation
{
    use EventDispatchingHttpCache;

    /**
     * @inheritdoc
     */
    public function __construct(
        HttpKernelInterface $kernel,
        StoreInterface $store,
        SurrogateInterface $surrogate = null,
        array $options = []
    ) {
        parent::__construct($kernel, $store, $surrogate, $options);

        $this->addSubscriber(new UserContextListener([
            'user_hash_uri' => '/widget/users/index/hash/',
            'session_name_prefix' => SessionHandlerInterface::SESSION_NAME,
        ]));
        $this->addSubscriber(new PurgeListener());
        $this->addSubscriber(new RefreshListener());
        $this->addSubscriber(new StaticAssetsListener());
        if (isset($options['debug']) && $options['debug']) {
            $this->addSubscriber(new DebugListener());
        }
    }

    /**
     * Made public to allow event listeners to do refresh operations.
     *
     * {@inheritDoc}
     */
    public function fetch(Request $request, $catch = false)
    {
        return parent::fetch($request, $catch);
    }
}
