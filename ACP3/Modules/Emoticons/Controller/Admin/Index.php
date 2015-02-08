<?php

namespace ACP3\Modules\Emoticons\Controller\Admin;

use ACP3\Core;
use ACP3\Modules\Emoticons;

/**
 * Class Index
 * @package ACP3\Modules\Emoticons\Controller\Admin
 */
class Index extends Core\Modules\Controller\Admin
{
    /**
     * @var \ACP3\Core\Helpers\Secure
     */
    protected $secureHelper;
    /**
     * @var \ACP3\Modules\Emoticons\Model
     */
    protected $emoticonsModel;
    /**
     * @var \ACP3\Modules\Emoticons\Validator
     */
    protected $emoticonsValidator;
    /**
     * @var \ACP3\Modules\Emoticons\Cache
     */
    protected $emoticonsCache;

    /**
     * @param \ACP3\Core\Context\Admin          $context
     * @param \ACP3\Core\Helpers\Secure         $secureHelper
     * @param \ACP3\Modules\Emoticons\Model     $emoticonsModel
     * @param \ACP3\Modules\Emoticons\Validator $emoticonsValidator
     * @param \ACP3\Modules\Emoticons\Cache     $emoticonsCache
     */
    public function __construct(
        Core\Context\Admin $context,
        Core\Helpers\Secure $secureHelper,
        Emoticons\Model $emoticonsModel,
        Emoticons\Validator $emoticonsValidator,
        Emoticons\Cache $emoticonsCache)
    {
        parent::__construct($context);

        $this->secureHelper = $secureHelper;
        $this->emoticonsModel = $emoticonsModel;
        $this->emoticonsValidator = $emoticonsValidator;
        $this->emoticonsCache = $emoticonsCache;
    }

    public function actionCreate()
    {
        if (empty($_POST) === false) {
            $this->_createPost($_POST);
        }

        $this->view->assign('form', array_merge(['code' => '', 'description' => ''], $_POST));

        $this->secureHelper->generateFormToken($this->request->query);
    }

    /**
     * @param array $formData
     */
    private function _createPost(array $formData)
    {
        try {
            $file = [];
            if (!empty($_FILES['picture']['tmp_name'])) {
                $file['tmp_name'] = $_FILES['picture']['tmp_name'];
                $file['name'] = $_FILES['picture']['name'];
                $file['size'] = $_FILES['picture']['size'];
            }

            $this->emoticonsValidator->validateCreate($formData, $file, $this->config->getSettings('emoticons'));

            $upload = new Core\Helpers\Upload('emoticons');
            $result = $upload->moveFile($file['tmp_name'], $file['name']);

            $insertValues = [
                'id' => '',
                'code' => Core\Functions::strEncode($formData['code']),
                'description' => Core\Functions::strEncode($formData['description']),
                'img' => $result['name'],
            ];

            $bool = $this->emoticonsModel->insert($insertValues);

            $this->emoticonsCache->setCache();

            $this->secureHelper->unsetFormToken($this->request->query);

            $this->redirectMessages()->setMessage($bool, $this->lang->t('system', $bool !== false ? 'create_success' : 'create_error'));
        } catch (Core\Exceptions\InvalidFormToken $e) {
            $this->redirectMessages()->setMessage(false, $e->getMessage());
        } catch (Core\Exceptions\ValidationFailed $e) {
            $this->view->assign('error_msg', $this->get('core.helpers.alerts')->errorBox($e->getMessage()));
        }
    }

    public function actionDelete()
    {
        $items = $this->_deleteItem();

        if ($this->request->action === 'confirmed') {
            $bool = false;

            $upload = new Core\Helpers\Upload('emoticons');
            foreach ($items as $item) {
                if (!empty($item) && $this->emoticonsModel->resultExists($item) === true) {
                    // Datei ebenfalls löschen
                    $file = $this->emoticonsModel->getOneImageById($item);
                    $upload->removeUploadedFile($file);
                    $bool = $this->emoticonsModel->delete($item);
                }
            }

            $this->emoticonsCache->setCache();

            $this->redirectMessages()->setMessage($bool, $this->lang->t('system', $bool !== false ? 'delete_success' : 'delete_error'));
        } elseif (is_string($items)) {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

    public function actionEdit()
    {
        $emoticon = $this->emoticonsModel->getOneById((int)$this->request->id);

        if (empty($emoticon) === false) {
            if (empty($_POST) === false) {
                $this->_editPost($_POST, $emoticon);
            }

            $this->view->assign('form', array_merge($emoticon, $_POST));

            $this->secureHelper->generateFormToken($this->request->query);
        } else {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

    /**
     * @param array $formData
     * @param array $emoticon
     */
    private function _editPost(array $formData, array $emoticon)
    {
        try {
            $file = [];
            if (!empty($_FILES['picture']['name'])) {
                $file['tmp_name'] = $_FILES['picture']['tmp_name'];
                $file['name'] = $_FILES['picture']['name'];
                $file['size'] = $_FILES['picture']['size'];
            }

            $this->emoticonsValidator->validateEdit($formData, $file, $this->config->getSettings('emoticons'));

            $updateValues = [
                'code' => Core\Functions::strEncode($formData['code']),
                'description' => Core\Functions::strEncode($formData['description']),
            ];

            if (empty($file) === false) {
                $upload = new Core\Helpers\Upload('emoticons');
                $upload->removeUploadedFile($emoticon['img']);
                $result = $upload->moveFile($file['tmp_name'], $file['name']);
                $updateValues['img'] = $result['name'];
            }

            $bool = $this->emoticonsModel->update($updateValues, $this->request->id);

            $this->emoticonsCache->setCache();

            $this->secureHelper->unsetFormToken($this->request->query);

            $this->redirectMessages()->setMessage($bool, $this->lang->t('system', $bool !== false ? 'edit_success' : 'edit_error'));
        } catch (Core\Exceptions\InvalidFormToken $e) {
            $this->redirectMessages()->setMessage(false, $e->getMessage());
        } catch (Core\Exceptions\ValidationFailed $e) {
            $this->view->assign('error_msg', $this->get('core.helpers.alerts')->errorBox($e->getMessage()));
        }
    }

    public function actionIndex()
    {
        $emoticons = $this->emoticonsModel->getAll();

        if (count($emoticons) > 0) {
            $canDelete = $this->acl->hasPermission('admin/emoticons/index/delete');
            $config = [
                'element' => '#acp-table',
                'sort_col' => $canDelete === true ? 4 : 3,
                'sort_dir' => 'desc',
                'hide_col_sort' => $canDelete === true ? 0 : '',
                'records_per_page' => $this->auth->entries
            ];
            $this->view->assign('datatable_config', $config);
            $this->view->assign('emoticons', $emoticons);
            $this->view->assign('can_delete', $canDelete);
        }
    }

    public function actionSettings()
    {
        if (empty($_POST) === false) {
            $this->_settingsPost($_POST);
        }

        $this->view->assign('form', array_merge($this->config->getSettings('emoticons'), $_POST));

        $this->secureHelper->generateFormToken($this->request->query);
    }

    /**
     * @param array $formData
     */
    private function _settingsPost(array $formData)
    {
        try {
            $this->emoticonsValidator->validateSettings($formData);

            $data = [
                'width' => (int)$formData['width'],
                'height' => (int)$formData['height'],
                'filesize' => (int)$formData['filesize'],
            ];
            $bool = $this->config->setSettings($data, 'emoticons');

            $this->secureHelper->unsetFormToken($this->request->query);

            $this->redirectMessages()->setMessage($bool, $this->lang->t('system', $bool === true ? 'settings_success' : 'settings_error'));
        } catch (Core\Exceptions\InvalidFormToken $e) {
            $this->redirectMessages()->setMessage(false, $e->getMessage());
        } catch (Core\Exceptions\ValidationFailed $e) {
            $this->view->assign('error_msg', $this->get('core.helpers.alerts')->errorBox($e->getMessage()));
        }
    }
}
