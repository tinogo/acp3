<?php

namespace ACP3\Core;

use ACP3\Core\Modules;
use ACP3\Core\Logger as ACP3Logger;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Patchwork\Utf8;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Bootstraps the application
 * @package ACP3
 */
class Application extends AbstractApplication
{
    /**
     * @var array
     */
    protected $systemSettings = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->defineDirConstants();

        if ($this->startupChecks()) {
            $this->setErrorHandler();
            $this->initializeClasses();
            $this->outputPage();
        }
    }

    /**
     * @inheritdoc
     */
    public function startupChecks()
    {
        // Standardzeitzone festlegen
        date_default_timezone_set('UTC');

        return $this->databaseConfigExists();
    }

    /**
     * @inheritdoc
     */
    public function defineDirConstants()
    {
        define('PHP_SELF', htmlentities($_SERVER['SCRIPT_NAME']));
        define('ROOT_DIR', substr(PHP_SELF, 0, strrpos(PHP_SELF, '/') + 1));
        define('HOST_NAME', 'http://' . $_SERVER['HTTP_HOST']);
        define('ROOT_DIR_ABSOLUTE', HOST_NAME . ROOT_DIR);
        define('ACP3_DIR', ACP3_ROOT_DIR . 'ACP3/');
        define('CLASSES_DIR', ACP3_DIR . 'Core/');
        define('MODULES_DIR', ACP3_DIR . 'Modules/');
        define('LIBRARIES_DIR', ACP3_ROOT_DIR . 'libraries/');
        define('UPLOADS_DIR', ACP3_ROOT_DIR . 'uploads/');
        define('CACHE_DIR', UPLOADS_DIR . 'cache/');
    }

    /**
     * Set monolog as the default PHP error handler
     */
    public function setErrorHandler()
    {
        $errorLevelMap = [
            E_ERROR => Logger::ERROR,
            E_WARNING => Logger::WARNING,
            E_PARSE => Logger::ERROR,
            E_NOTICE => Logger::NOTICE,
            E_CORE_ERROR => Logger::ERROR,
            E_CORE_WARNING => Logger::WARNING,
            E_COMPILE_ERROR => Logger::ERROR,
            E_COMPILE_WARNING => Logger::WARNING,
            E_USER_ERROR => Logger::ERROR,
            E_USER_WARNING => Logger::WARNING,
            E_USER_NOTICE => Logger::NOTICE,
            E_STRICT => Logger::WARNING,
            E_RECOVERABLE_ERROR => Logger::ERROR,
            E_DEPRECATED => Logger::WARNING,
            E_USER_DEPRECATED => Logger::WARNING,
        ];

        $stream = new StreamHandler(UPLOADS_DIR . 'logs/system.log', Logger::NOTICE);
        $stream->setFormatter(new LineFormatter(null, null, true));

        $logger = new Logger('system', [$stream]);
        ErrorHandler::register($logger, $errorLevelMap);
    }

    /**
     * Checks, whether the maintenance mode is active
     *
     * @param \ACP3\Core\RequestInterface $request
     *
     * @return bool
     */
    private function maintenanceModeIsEnabled(RequestInterface $request)
    {
        if ((bool)$this->systemSettings['maintenance_mode'] === true &&
            $request->getArea() !== 'admin' &&
            strpos($request->getQuery(), 'users/index/login/') !== 0
        ) {
            header('HTTP/1.0 503 Service Unavailable');

            $view = $this->container->get('core.view');
            $view->assign('PAGE_TITLE', 'ACP3');
            $view->assign('ROOT_DIR', ROOT_DIR);
            $view->assign('CONTENT', $this->systemSettings['maintenance_message']);
            $view->displayTemplate('system/maintenance.tpl');

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function initializeClasses()
    {
        Utf8\Bootup::initAll(); // Enables the portability layer and configures PHP for UTF-8
        Utf8\Bootup::filterRequestUri(); // Redirects to an UTF-8 encoded URL if it's not already the case
        Utf8\Bootup::filterRequestInputs(); // Normalizes HTTP inputs to UTF-8 NFC

        $file = CACHE_DIR . 'sql/container.php';
        $containerConfigCache = new ConfigCache($file, (defined('DEBUG') && DEBUG === true));

        if (!$containerConfigCache->isFresh()) {
            $containerBuilder = new ContainerBuilder();
            $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
            $loader->load(CLASSES_DIR . 'config/services.yml');
            $loader->load(CLASSES_DIR . 'View/Renderer/Smarty/config/services.yml');

            // Try to get all available services
            /** @var Modules $modules */
            $modules = $containerBuilder->get('core.modules');
            $activeModules = $modules->getActiveModules();
            $moduleNamespaces = $modules->getModuleNamespaces();

            foreach ($activeModules as $module) {
                foreach ($moduleNamespaces as $namespace) {
                    $path = MODULES_DIR . $namespace . '/' . $module['dir'] . '/config/services.yml';

                    if (is_file($path)) {
                        $loader->load($path);
                    }
                }
            }

            $containerBuilder->compile();

            $dumper = new PhpDumper($containerBuilder);
            $containerConfigCache->write(
                $dumper->dump(['class' => 'ACP3ServiceContainer']),
                $containerBuilder->getResources()
            );
        }

        require_once $file;
        $this->container = new \ACP3ServiceContainer();

        // Load system settings
        $this->systemSettings = $this->container->get('core.config')->getSettings('system');

        $this->container->get('core.auth')->authenticate();

        $this->_setThemeConstants();

        $this->container->get('core.view')->setRenderer('smarty');
    }

    /**
     * Pfade zum Theme setzen
     */
    private function _setThemeConstants()
    {
        define('DESIGN_PATH', ROOT_DIR . 'designs/' . $this->systemSettings['design'] . '/');
        define('DESIGN_PATH_INTERNAL', ACP3_ROOT_DIR . 'designs/' . $this->systemSettings['design'] . '/');
        define('DESIGN_PATH_ABSOLUTE', HOST_NAME . DESIGN_PATH);
    }

    /**
     * @inheritdoc
     */
    public function outputPage()
    {
        /** @var \ACP3\Core\Request $request */
        $request = $this->container->get('core.request');
        $request->processQuery();

        if ($this->maintenanceModeIsEnabled($request)) {
            return;
        }

        /** @var \ACP3\Core\Redirect $redirect */
        $redirect = $this->container->get('core.redirect');

        try {
            $frontController = new FrontController($this->container);
            $frontController->dispatch();
        } catch (Exceptions\ResultNotExists $e) {
            if ($e->getMessage()) {
                ACP3Logger::error('404', $e);
            } else {
                ACP3Logger::error('404', 'Could not find any results for request: ' . $request->getQuery());
            }

            $redirect->temporary('errors/index/404');
        } catch (Exceptions\UnauthorizedAccess $e) {
            $redirectUri = base64_encode($request->getOriginalQuery());
            $redirect->temporary('users/index/login/redirect_' . $redirectUri);
        } catch (Exceptions\AccessForbidden $e) {
            $redirect->temporary('errors/index/403');
        } catch (Exceptions\ControllerActionNotFound $e) {
            ACP3Logger::error('404', 'Request: ' . $request->getQuery());
            ACP3Logger::error('404', $e);

            $this->handleException($e, $redirect, 'errors/index/404');
        } catch (\Exception $e) {
            ACP3Logger::error('exception', $e);

            $this->handleException($e, $redirect, 'errors/index/500');
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Renders an exception
     *
     * @param string $errorMessage
     */
    private function _renderApplicationException($errorMessage)
    {
        $view = $this->container->get('core.view');
        $view->assign('ROOT_DIR', ROOT_DIR);
        $view->assign('PAGE_TITLE', 'ACP3');
        $view->assign('CONTENT', $errorMessage);
        $view->displayTemplate('system/exception.tpl');
    }

    /**
     * @param \Exception          $exception
     * @param \ACP3\Core\Redirect $redirect
     * @param string              $path
     */
    protected function handleException(\Exception $exception, Redirect $redirect, $path)
    {
        if (defined('DEBUG') && DEBUG === true) {
            $errorMessage = $exception->getMessage();
            $this->_renderApplicationException($errorMessage);
        } else {
            $redirect->temporary($path);
        }
    }
}
