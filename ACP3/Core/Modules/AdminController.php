<?php

namespace ACP3\Core\Modules;

use ACP3\Core;
use ACP3\Core\Modules\Controller\Context;

/**
 * Class AdminController
 * @package ACP3\Core\Modules
 */
abstract class AdminController extends Core\Modules\FrontendController
{
    /**
     * @var \ACP3\Core\SessionHandler
     */
    protected $session;
    /**
     * @var \ACP3\Core\Validator\Rules\Misc
     */
    protected $validate;

    /**
     * @param \ACP3\Core\Modules\Controller\AdminContext $adminContext
     */
    public function __construct(Controller\AdminContext $adminContext)
    {
        parent::__construct($adminContext);

        $this->validate = $adminContext->getValidate();
        $this->session = $adminContext->getSession();
    }

    /**
     * @return $this
     * @throws \ACP3\Core\Exceptions\UnauthorizedAccess
     */
    public function preDispatch()
    {
        if ($this->auth->isUser() === false) {
            throw new Core\Exceptions\UnauthorizedAccess();
        }

        return parent::preDispatch();
    }

    /**
     * @param string      $action
     * @param callable    $callback
     * @param string|null $moduleConfirmUrl
     * @param string|null $moduleIndexUrl
     *
     * @throws \ACP3\Core\Exceptions\ResultNotExists
     */
    protected function handleDeleteAction(
        $action,
        callable $callback,
        $moduleConfirmUrl = null,
        $moduleIndexUrl = null
    )
    {
        $this->handleCustomDeleteAction(
            $action,
            function ($items) use ($callback) {
                $callback($items);
            },
            $moduleConfirmUrl,
            $moduleIndexUrl
        );
    }

    /**
     * @param string      $action
     * @param callable    $callback
     * @param string|null $moduleConfirmUrl
     * @param string|null $moduleIndexUrl
     *
     * @throws \ACP3\Core\Exceptions\ResultNotExists
     */
    protected function handleCustomDeleteAction(
        $action,
        callable $callback,
        $moduleConfirmUrl = null,
        $moduleIndexUrl = null
    )
    {
        list($moduleConfirmUrl, $moduleIndexUrl) = $this->generateDefaultConfirmationBoxUris($moduleConfirmUrl, $moduleIndexUrl);
        $result = $this->_deleteItem($action, $moduleConfirmUrl, $moduleIndexUrl);

        if (is_string($result)) {
            $this->setTemplate($result);
        } elseif ($action === 'confirmed' && is_array($result)) {
            $callback($result);
        } else {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

    /**
     * Little helper function for deleting an result set
     *
     * @param string      $action
     * @param string|null $moduleConfirmUrl
     * @param string|null $moduleIndexUrl
     *
     * @return string|array
     */
    private function _deleteItem($action, $moduleConfirmUrl = null, $moduleIndexUrl = null)
    {
        if (is_array($this->request->getPost()->get('entries')) === true) {
            $entries = $this->request->getPost()->get('entries');
        } elseif ((bool)preg_match('/^((\d+)\|)*(\d+)$/', $this->request->getParameters()->get('entries')) === true) {
            $entries = $this->request->getParameters()->get('entries');
        }

        /** @var \ACP3\Core\Helpers\Alerts $alerts */
        $alerts = $this->get('core.helpers.alerts');

        if (empty($entries)) {
            return $alerts->errorBoxContent($this->lang->t('system', 'no_entries_selected'));
        } elseif (empty($entries) === false && $action !== 'confirmed') {
            if (is_array($entries) === false) {
                $entries = [$entries];
            }

            $data = [
                'action' => 'confirmed',
                'entries' => $entries
            ];

            return $alerts->confirmBoxPost(
                $this->fetchConfirmationBoxText($entries),
                $data,
                $this->router->route($moduleConfirmUrl),
                $this->router->route($moduleIndexUrl)
            );
        } else {
            return is_array($entries) ? $entries : explode('|', $entries);
        }
    }

    /**
     * @param string|null $moduleConfirmUrl
     * @param string|null $moduleIndexUrl
     *
     * @return array
     */
    protected function generateDefaultConfirmationBoxUris($moduleConfirmUrl, $moduleIndexUrl)
    {
        if ($moduleConfirmUrl === null) {
            $moduleConfirmUrl = $this->request->getFullPath();
        }

        if ($moduleIndexUrl === null) {
            $moduleIndexUrl = $this->request->getModuleAndController();
        }

        return [$moduleConfirmUrl, $moduleIndexUrl];
    }

    /**
     * @param callable    $callback
     * @param null|string $path
     */
    protected function handleSettingsPostAction(callable $callback, $path = null)
    {
        $this->handlePostAction(function () use ($callback, $path) {
            $result = $callback();

            $this->setRedirectMessageAfterPost($result, 'settings', $path);
        }, $path);
    }

    /**
     * @param callable    $callback
     * @param null|string $path
     */
    protected function handleCreatePostAction(callable $callback, $path = null)
    {
        $this->handlePostAction(function () use ($callback, $path) {
            $result = $callback();

            $this->setRedirectMessageAfterPost($result, 'create', $path);
        });
    }

    /**
     * @param callable    $callback
     * @param null|string $path
     */
    protected function handleEditPostAction(callable $callback, $path = null)
    {
        $this->handlePostAction(function () use ($callback, $path) {
            $result = $callback();

            $this->setRedirectMessageAfterPost($result, 'edit', $path);
        });
    }

    /**
     * @param bool|int    $result
     * @param string      $localization
     * @param null|string $path
     */
    private function setRedirectMessageAfterPost($result, $localization, $path = null)
    {
        $this->redirectMessages()->setMessage(
            $result,
            $this->lang->t('system', $localization . ($result !== false ? 'success' : 'error')),
            $path
        );
    }

    /**
     * @param array $entries
     *
     * @return mixed|string
     */
    protected function fetchConfirmationBoxText($entries)
    {
        $entriesCount = count($entries);

        if ($entriesCount === 1) {
            return $this->lang->t('system', 'confirm_delete_single');
        }

        return str_replace('{items}', $entriesCount, $this->lang->t('system', 'confirm_delete_multiple'));
    }
}
