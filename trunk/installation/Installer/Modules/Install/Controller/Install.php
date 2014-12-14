<?php

namespace ACP3\Installer\Modules\Install\Controller;

use ACP3\Core\Config;
use ACP3\Installer\Core\Date;
use ACP3\Core\Exceptions\ValidationFailed;
use ACP3\Core\Helpers\Secure;
use ACP3\Installer\Core;
use ACP3\Installer\Modules\Install\Helpers;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class Install
 * @package ACP3\Installer\Modules\Install\Controller
 */
class Install extends AbstractController
{
    /**
     * @var string
     */
    protected $configFilePath = '';

    /**
     * @var Date
     */
    protected $date;
    /**
     * @var \ACP3\Core\DB
     */
    protected $db;
    /**
     * @var Helpers
     */
    protected $installHelper;

    /**
     * @param Core\Context $context
     * @param Date $date
     * @param Helpers $installHelper
     */
    public function __construct(
        Core\Context $context,
        Date $date,
        Helpers $installHelper
    )
    {
        parent::__construct($context);

        $this->date = $date;
        $this->installHelper = $installHelper;
        $this->configFilePath = ACP3_DIR . 'config/config.yml';
    }

    public function actionIndex()
    {
        if (empty($_POST) === false) {
            $this->_indexPost($_POST);
        }

        // Zeitzonen
        $this->view->assign('time_zones', $this->date->getTimeZones(date_default_timezone_get()));

        $defaults = [
            'db_host' => 'localhost',
            'db_pre' => 'acp3_',
            'db_user' => '',
            'db_name' => '',
            'user_name' => 'admin',
            'mail' => '',
            'date_format_long' => $this->date->getDateFormatLong(),
            'date_format_short' => $this->date->getDateFormatShort(),
            'title' => 'ACP3',
        ];

        $this->view->assign('form', array_merge($defaults, $_POST));
    }

    /**
     * @param array $formData
     */
    private function _indexPost(array $formData)
    {
        try {
            $validator = $this->get('install.validator');
            $validator->validateConfiguration($formData, $this->configFilePath);

            $this->_writeConfigFile($formData);
            $this->_setContainer();
            $bool = $this->_installModules();

            // Admin-User, Menüpunkte, News, etc. in die DB schreiben
            if ($bool === true) {
                $this->_installSampleData($formData);
            }

            $this->setTemplate('install/install.result.tpl');
            return;
        } catch (ValidationFailed $e) {
            $this->view->assign('error_msg', $this->get('core.helpers.alerts')->errorBox($e->getMessage()));
        }
    }

    /**
     * @param array $formData
     */
    private function _writeConfigFile(array $formData)
    {
        // Systemkonfiguration erstellen
        $configParams = [
            'parameters' => [
                'db_host' => $formData['db_host'],
                'db_name' => $formData['db_name'],
                'db_table_prefix' => $formData['db_pre'],
                'db_password' => $formData['db_password'],
                'db_user' => $formData['db_user'],
                'db_driver' => 'pdo_mysql',
                'db_charset' => 'utf8'
            ]
        ];

        $this->installHelper->writeConfigFile($this->configFilePath, $configParams);
    }

    /**
     * @throws \Exception
     */
    private function _setContainer()
    {
        $this->container = new ContainerBuilder();

        $loader = new YamlFileLoader($this->container, new FileLocator(__DIR__));
        $loader->load(ACP3_DIR . 'config/services.yml');
        $loader->load(INSTALLER_ACP3_DIR . 'config/services.yml');
        $loader->load(INSTALLER_CLASSES_DIR . 'View/Renderer/Smarty/services.yml');

        // Load installer modules services
        $installerModules = array_diff(scandir(INSTALLER_MODULES_DIR), ['.', '..']);
        foreach ($installerModules as $module) {
            $path = INSTALLER_MODULES_DIR . $module . '/config/services.yml';
            if (is_file($path) === true) {
                $loader->load($path);
            }
        }

        $modules = array_diff(scandir(MODULES_DIR), ['.', '..']);
        foreach ($modules as $module) {
            $path = MODULES_DIR . $module . '/config/services.yml';
            if (is_file($path) === true) {
                $loader->load($path);
            }
        }

        $this->container->setParameter('cache_driver', 'Array');

        $this->container->compile();
    }

    /**
     * @return bool
     */
    private function _installModules()
    {
        $bool = false;
        // Install core modules
        $installFirst = ['system', 'permissions', 'users'];
        foreach ($installFirst as $module) {
            $bool = $this->installHelper->installModule($module, $this->container);
            if ($bool === false) {
                $this->view->assign('install_error', true);
                break;
            }
        }

        // Install "normal" modules
        if ($bool === true) {
            $modules = array_diff(scandir(MODULES_DIR), ['.', '..']);

            foreach ($modules as $module) {
                $module = strtolower($module);
                if (in_array(strtolower($module), $installFirst) === false) {
                    if ($this->installHelper->installModule($module, $this->container) === false) {
                        $this->view->assign('install_error', true);
                        break;
                    }
                }
            }
        }

        return $bool;
    }

    /**
     * @param array $formData
     */
    private function _installSampleData(array $formData)
    {
        /** @var \ACP3\Core\DB db */
        $this->db = $this->get('core.db');

        $securityHelper = $this->get('core.helpers.secure');
        $salt = $securityHelper->salt(12);
        $currentDate = gmdate('Y-m-d H:i:s');

        $newsModuleId = $this->db->getConnection()->fetchColumn('SELECT id FROM ' . $this->db->getPrefix() . 'modules WHERE NAME = ?', ['news']);
        $queries = [
            "INSERT INTO `{pre}users` VALUES ('', 1, " . $this->db->getConnection()->quote($formData["user_name"]) . ", '" . $securityHelper->generateSaltedPassword($salt, $formData["user_pwd"]) . ":" . $salt . "', 0, '', '1', '', 0, '" . $formData["mail"] . "', 0, '', '', '', '', '', '', '', '', 0, 0, " . $this->db->getConnection()->quote($formData["date_format_long"]) . ", " . $this->db->getConnection()->quote($formData["date_format_short"]) . ", '" . $formData["date_time_zone"] . "', '" . LANG . "', '20', '', '" . $currentDate . "');",
            'INSERT INTO `{pre}categories` VALUES (\'\', \'' . $this->lang->t('install', 'category_name') . '\', \'\', \'' . $this->lang->t('install', 'category_description') . '\', \'' . $newsModuleId . '\');',
            'INSERT INTO `{pre}news` VALUES (\'\', \'' . $currentDate . '\', \'' . $currentDate . '\', \'' . $this->lang->t('install', 'news_headline') . '\', \'' . $this->lang->t('install', 'news_text') . '\', \'1\', \'1\', \'1\', \'\', \'\', \'\', \'\');',
            'INSERT INTO `{pre}menu_items` VALUES (\'\', 1, 1, 1, 0, 1, 4, 1, \'' . $this->lang->t('install', 'pages_news') . '\', \'news\', 1);',
            'INSERT INTO `{pre}menu_items` VALUES (\'\', 1, 1, 1, 1, 2, 3, 1, \'' . $this->lang->t('install', 'pages_newsletter') . '\', \'newsletter\', 1);',
            'INSERT INTO `{pre}menu_items` VALUES (\'\', 1, 1, 3, 0, 5, 6, 1, \'' . $this->lang->t('install', 'pages_files') . '\', \'files\', 1);',
            'INSERT INTO `{pre}menu_items` VALUES (\'\', 1, 1, 4, 0, 7, 8, 1, \'' . $this->lang->t('install', 'pages_gallery') . '\', \'gallery\', 1);',
            'INSERT INTO `{pre}menu_items` VALUES (\'\', 1, 1, 5, 0, 9, 10, 1, \'' . $this->lang->t('install', 'pages_guestbook') . '\', \'guestbook\', 1);',
            'INSERT INTO `{pre}menu_items` VALUES (\'\', 1, 1, 6, 0, 11, 12, 1, \'' . $this->lang->t('install', 'pages_polls') . '\', \'polls\', 1);',
            'INSERT INTO `{pre}menu_items` VALUES (\'\', 1, 1, 7, 0, 13, 14, 1, \'' . $this->lang->t('install', 'pages_search') . '\', \'search\', 1);',
            'INSERT INTO `{pre}menu_items` VALUES (\'\', 1, 2, 8, 0, 15, 16, 1, \'' . $this->lang->t('install', 'pages_contact') . '\', \'contact\', 1);',
            'INSERT INTO `{pre}menu_items` VALUES (\'\', 2, 2, 9, 0, 17, 18, 1, \'' . $this->lang->t('install', 'pages_imprint') . '\', \'contact/index/imprint/\', 1);',
            'INSERT INTO `{pre}menus` VALUES (1, \'main\', \'' . $this->lang->t('install', 'pages_main') . '\');',
            'INSERT INTO `{pre}menus` VALUES (2, \'sidebar\', \'' . $this->lang->t('install', 'pages_sidebar') . '\');',
        ];

        if ($this->installHelper->executeSqlQueries($queries, $this->db) === false) {
            $this->view->assign('install_error', true);
        }

        // Modulkonfigurationsdateien schreiben
        $systemSettings = [
            'date_format_long' => \ACP3\Core\Functions::strEncode($formData['date_format_long']),
            'date_format_short' => \ACP3\Core\Functions::strEncode($formData['date_format_short']),
            'date_time_zone' => $formData['date_time_zone'],
            'maintenance_message' => $this->lang->t('install', 'offline_message'),
            'lang' => LANG
        ];

        $configSystem = $this->get('system.config');
        $configSystem->setSettings($systemSettings);

        $configSeo = $this->get('seo.config');
        $configSeo->setSettings(['title' => !empty($formData['title']) ? $formData['title'] : 'ACP3']);

        $configUsers = $this->get('users.config');
        $configUsers->setSettings(['mail' => $formData['mail']]);

        $configContact = $this->get('contact.config');
        $configContact->setSettings(['mail' => $formData['mail'], 'disclaimer' => $this->lang->t('install', 'disclaimer')]);

        $configNewsletter = $this->get('newsletter.config');
        $configNewsletter->setSettings(['mail' => $formData['mail'], 'mailsig' => $this->lang->t('install', 'sincerely') . "\n\n" . $this->lang->t('install', 'newsletter_mailsig')]);
    }
}
