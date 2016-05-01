<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Installer\Core\Controller;

use ACP3\Core\Controller\ActionInterface;
use ACP3\Core\Controller\DisplayActionTrait;
use ACP3\Core\Filesystem;
use ACP3\Core\Redirect;

/**
 * Module Controller of the installer modules
 * @package ACP3\Installer\Core\Controller
 */
abstract class AbstractInstallerAction implements ActionInterface
{
    use DisplayActionTrait;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    /**
     * @var \ACP3\Installer\Core\I18n\Translator
     */
    protected $translator;
    /**
     * @var \ACP3\Core\RouterInterface
     */
    protected $router;
    /**
     * @var \ACP3\Installer\Core\Environment\ApplicationPath
     */
    protected $appPath;
    /**
     * @var \ACP3\Core\Http\RequestInterface
     */
    protected $request;
    /**
     * @var \ACP3\Core\View
     */
    protected $view;
    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;
    /**
     * @var string
     */
    private $layout = 'layout.tpl';

    /**
     * @param \ACP3\Installer\Core\Controller\Context\InstallerContext $context
     */
    public function __construct(Context\InstallerContext $context)
    {
        $this->container = $context->getContainer();
        $this->translator = $context->getTranslator();
        $this->request = $context->getRequest();
        $this->router = $context->getRouter();
        $this->view = $context->getView();
        $this->response = $context->getResponse();
        $this->appPath = $context->getAppPath();
    }

    /**
     * @inheritdoc
     */
    public function preDispatch()
    {
        $this->setLanguage();

        // Einige Template Variablen setzen
        $this->view->assign('LANGUAGES', $this->languagesDropdown($this->translator->getLocale()));
        $this->view->assign('PHP_SELF', $this->appPath->getPhpSelf());
        $this->view->assign('REQUEST_URI', $this->request->getServer()->get('REQUEST_URI'));
        $this->view->assign('ROOT_DIR', $this->appPath->getWebRoot());
        $this->view->assign('INSTALLER_ROOT_DIR', $this->appPath->getInstallerWebRoot());
        $this->view->assign('DESIGN_PATH', $this->appPath->getDesignPathWeb());
        $this->view->assign('UA_IS_MOBILE', $this->request->getUserAgent()->isMobileBrowser());
        $this->view->assign('IS_AJAX', $this->request->isAjax());

        $languageInfo = simplexml_load_file(
            $this->appPath->getInstallerModulesDir() . 'Install/Resources/i18n/' . $this->translator->getLocale() . '.xml'
        );
        $this->view->assign(
            'LANG_DIRECTION',
            isset($languageInfo->info->direction) ? $languageInfo->info->direction : 'ltr'
        );
        $this->view->assign('LANG', $this->translator->getShortIsoCode());
    }

    /**
     * @return Redirect
     */
    public function redirect()
    {
        return $this->get('core.redirect');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getResponse()
    {
        return $this->response;
    }

    /**
     * @return \ACP3\Core\View
     */
    protected function getView()
    {
        return $this->view;
    }

    /**
     * @inheritdoc
     */
    public function get($serviceId)
    {
        return $this->container->get($serviceId);
    }

    /**
     * Generiert das Dropdown-Menü mit der zur Verfügung stehenden Installersprachen
     *
     * @param string $selectedLanguage
     *
     * @return array
     */
    private function languagesDropdown($selectedLanguage)
    {
        $languages = [];
        $path = $this->appPath->getInstallerModulesDir() . 'Install/Resources/i18n/';

        foreach (Filesystem::scandir($path) as $row) {
            $langInfo = simplexml_load_file($path . $row);
            if (!empty($langInfo)) {
                $languages[] = [
                    'language' => substr($row, 0, -4),
                    'selected' => $selectedLanguage === substr($row, 0, -4) ? ' selected="selected"' : '',
                    'name' => $langInfo->info->name
                ];
            }
        }
        return $languages;
    }

    /**
     * @inheritdoc
     */
    protected function applyTemplateAutomatically()
    {
        return $this->request->getModule() . '/' . $this->request->getController() . '.' . $this->request->getAction() . '.tpl';
    }

    protected function addCustomTemplateVarsBeforeOutput()
    {
        $this->view->assign('PAGE_TITLE', $this->translator->t('install', 'acp3_installation'));
        $this->view->assign('TITLE', $this->translator->t(
            $this->request->getModule(),
            $this->request->getController() . '_' . $this->request->getAction())
        );
        $this->view->assign('LAYOUT', $this->request->isAjax() ? 'ajax.tpl' : $this->getLayout());
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param string $layout
     * @return $this
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    private function setLanguage()
    {
        $cookieLocale = $this->request->getCookies()->get('ACP3_INSTALLER_LANG', '');
        if (!preg_match('=/=', $cookieLocale) &&
            is_file($this->appPath->getInstallerModulesDir() . 'Install/Resources/i18n/' . $cookieLocale . '.xml') === true
        ) {
            $language = $cookieLocale;
        } else {
            $language = 'en_US'; // Fallback language

            foreach ($this->request->getUserAgent()->parseAcceptLanguage() as $locale => $val) {
                $locale = str_replace('-', '_', $locale);
                if ($this->translator->languagePackExists($locale) === true) {
                    $language = $locale;
                    break;
                }
            }
        }

        $this->translator->setLocale($language);
    }
}
