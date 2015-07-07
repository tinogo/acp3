<?php
namespace ACP3\Core;

/**
 * Class AbstractApplication
 * @package ACP3\Core
 */
abstract class AbstractApplication implements ApplicationInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @inheritdoc
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Checks, whether the database configuration file exists
     *
     * @return bool
     */
    protected function databaseConfigExists()
    {
        $path = ACP3_DIR . 'config.yml';
        if (is_file($path) === false || filesize($path) === 0) {
            echo 'The ACP3 is not correctly installed. Please navigate to the <a href="' . ROOT_DIR . 'installation/">installation wizard</a> and follow its instructions.';
            return false;
        }

        return true;
    }

}