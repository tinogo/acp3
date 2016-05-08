<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Core\Authentication;

use ACP3\Core\Authentication\Exception\InvalidAuthenticationMethodException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AuthenticationFactory
 * @package ACP3\Core\Authentication
 */
class AuthenticationFactory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $authenticationMethod
     *
     * @return \ACP3\Core\Authentication\AuthenticationInterface
     * @throws \ACP3\Core\Authentication\Exception\InvalidAuthenticationMethodException
     */
    public function get($authenticationMethod)
    {
        $serviceId = 'core.authentication.' . $authenticationMethod;
        if ($this->container->has($serviceId)) {
            return $this->container->get($serviceId);
        }

        throw new InvalidAuthenticationMethodException();
    }
}
