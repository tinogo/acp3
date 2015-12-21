<?php
namespace ACP3\Core\Application;

/**
 * Interface BootstrapInterface
 * @package ACP3\Core\Application
 */
interface BootstrapInterface
{
    /**
     * Contains the current ACP3 version string
     */
    const VERSION = '4.0-dev';

    /**
     * Executes the application bootstrapping process and outputs the requested page
     */
    function run();

    /**
     * Performs some startup checks
     */
    function startUpChecks();

    /**
     * Initializes the dependency injection container
     */
    function initializeClasses();

    /**
     * Handle the request and output the page
     */
    function outputPage();

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    function getContainer();
}