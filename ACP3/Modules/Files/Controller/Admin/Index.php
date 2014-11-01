<?php

namespace ACP3\Modules\Files\Controller\Admin;

use ACP3\Core;
use ACP3\Modules\Categories;
use ACP3\Modules\Files;

/**
 * Class Index
 * @package ACP3\Modules\Files\Controller\Admin
 */
class Index extends Core\Modules\Controller\Admin
{
    /**
     * @var Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Core\Helpers\Secure
     */
    protected $secureHelper;
    /**
     * @var Files\Model
     */
    protected $filesModel;
    /**
     * @var Files\Cache
     */
    protected $filesCache;
    /**
     * @var Core\Config
     */
    protected $filesConfig;

    public function __construct(
        Core\Context\Admin $context,
        Core\Date $date,
        Core\Helpers\Secure $secureHelper,
        Files\Model $filesModel,
        Files\Cache $filesCache,
        Core\Config $filesConfig)
    {
        parent::__construct($context);

        $this->date = $date;
        $this->secureHelper = $secureHelper;
        $this->filesModel = $filesModel;
        $this->filesCache = $filesCache;
        $this->filesConfig = $filesConfig;
    }

    public function actionCreate()
    {
        $settings = $this->filesConfig->getSettings();

        if (empty($_POST) === false) {
            $this->_createPost($_POST, $settings);
        }

        // Datumsauswahl
        $this->view->assign('publication_period', $this->date->datepicker(array('start', 'end')));

        $units = array('Byte', 'KiB', 'MiB', 'GiB', 'TiB');
        $this->view->assign('units', Core\Functions::selectGenerator('units', $units, $units, ''));

        // Formularelemente
        $this->view->assign('categories', $this->get('categories.helpers')->categoriesList('files', '', true));

        if ($settings['comments'] == 1 && $this->modules->isActive('comments') === true) {
            $options = array();
            $options[0]['name'] = 'comments';
            $options[0]['checked'] = Core\Functions::selectEntry('comments', '1', '0', 'checked');
            $options[0]['lang'] = $this->lang->t('system', 'allow_comments');
            $this->view->assign('options', $options);
        }

        $this->view->assign('checked_external', isset($_POST['external']) ? ' checked="checked"' : '');

        $defaults = array(
            'title' => '',
            'file_internal' => '',
            'file_external' => '',
            'filesize' => '',
            'text' => '',
            'alias' => '',
            'seo_keywords' => '',
            'seo_description' => '',
        );

        $this->view->assign('SEO_FORM_FIELDS', $this->seo->formFields());

        $this->view->assign('form', array_merge($defaults, $_POST));

        $this->secureHelper->generateFormToken($this->request->query);
    }

    public function actionDelete()
    {
        $items = $this->_deleteItem('acp/files/index/delete', 'acp/files');

        if ($this->request->action === 'confirmed') {
            $bool = false;
            $commentsInstalled = $this->modules->isInstalled('comments');

            $cache = new Core\Cache('files');
            $upload = new Core\Helpers\Upload('files');
            foreach ($items as $item) {
                if (!empty($item)) {
                    $upload->removeUploadedFile($this->filesModel->getFileById($item)); // Datei ebenfalls löschen
                    $bool = $this->filesModel->delete($item);
                    if ($commentsInstalled === true) {
                        $this->get('comments.helpers')->deleteCommentsByModuleAndResult('files', $item);
                    }

                    $cache->delete(Files\Cache::CACHE_ID);
                    $this->aliases->deleteUriAlias(sprintf(Files\Helpers::URL_KEY_PATTERN, $item));
                }
            }

            $this->seo->setCache();

            $this->redirectMessages()->setMessage($bool, $this->lang->t('system', $bool !== false ? 'delete_success' : 'delete_error'), 'acp/files');
        } elseif (is_string($items)) {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

    public function actionEdit()
    {
        $dl = $this->filesModel->getOneById((int)$this->request->id);

        if (empty($dl) === false) {
            $settings = $this->filesConfig->getSettings();

            if (empty($_POST) === false) {
                $this->_editPost($_POST, $settings, $dl);
            }

            // Datumsauswahl
            $this->view->assign('publication_period', $this->date->datepicker(array('start', 'end'), array($dl['start'], $dl['end'])));

            $units = array('Byte', 'KiB', 'MiB', 'GiB', 'TiB');
            $this->view->assign('units', Core\Functions::selectGenerator('units', $units, $units, trim(strrchr($dl['size'], ' '))));

            $dl['filesize'] = substr($dl['size'], 0, strpos($dl['size'], ' '));

            // Formularelemente
            $this->view->assign('categories', $this->get('categories.helpers')->categoriesList('files', $dl['category_id'], true));

            if ($settings['comments'] == 1 && $this->modules->isActive('comments') === true) {
                $options = array();
                $options[0]['name'] = 'comments';
                $options[0]['checked'] = Core\Functions::selectEntry('comments', '1', $dl['comments'], 'checked');
                $options[0]['lang'] = $this->lang->t('system', 'allow_comments');
                $this->view->assign('options', $options);
            }

            $this->view->assign('checked_external', isset($_POST['external']) ? ' checked="checked"' : '');
            $this->view->assign('current_file', $dl['file']);

            $this->view->assign('SEO_FORM_FIELDS', $this->seo->formFields(sprintf(Files\Helpers::URL_KEY_PATTERN, $this->request->id)));
            $this->view->assign('form', array_merge($dl, $_POST));

            $this->secureHelper->generateFormToken($this->request->query);
        } else {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

    public function actionIndex()
    {
        $this->redirectMessages()->getMessage();

        $files = $this->filesModel->getAllInAcp();
        $c_files = count($files);

        if ($c_files > 0) {
            $canDelete = $this->modules->hasPermission('admin/files/index/delete');
            $config = array(
                'element' => '#acp-table',
                'sort_col' => $canDelete === true ? 1 : 0,
                'sort_dir' => 'desc',
                'hide_col_sort' => $canDelete === true ? 0 : '',
                'records_per_page' => $this->auth->entries
            );
            $this->appendContent($this->get('core.functions')->dataTable($config));

            $this->view->assign('files', $files);
            $this->view->assign('can_delete', $canDelete);
        }
    }

    public function actionSettings()
    {
        if (empty($_POST) === false) {
            $this->_settingsPost($_POST);
        }

        $settings = $this->filesConfig->getSettings();

        if ($this->modules->isActive('comments') === true) {
            $lang_comments = array($this->lang->t('system', 'yes'), $this->lang->t('system', 'no'));
            $this->view->assign('comments', Core\Functions::selectGenerator('comments', array(1, 0), $lang_comments, $settings['comments'], 'checked'));
        }

        $this->view->assign('dateformat', $this->date->dateFormatDropdown($settings['dateformat']));

        $this->view->assign('sidebar_entries', Core\Functions::recordsPerPage((int)$settings['sidebar'], 1, 10));

        $this->secureHelper->generateFormToken($this->request->query);
    }

    private function _createPost(array $formData, array $settings)
    {
        try {
            if (isset($formData['external'])) {
                $file = $formData['file_external'];
            } else {
                $file = array();
                $file['tmp_name'] = $_FILES['file_internal']['tmp_name'];
                $file['name'] = $_FILES['file_internal']['name'];
                $file['size'] = $_FILES['file_internal']['size'];
            }

            $validator = $this->get('files.validator');
            $validator->validateCreate($formData, $file);

            if (is_array($file) === true) {
                $upload = new Core\Helpers\Upload('files');
                $result = $upload->moveFile($file['tmp_name'], $file['name']);
                $newFile = $result['name'];
                $filesize = $result['size'];
            } else {
                $formData['filesize'] = (float)$formData['filesize'];
                $newFile = $file;
                $filesize = $formData['filesize'] . ' ' . $formData['unit'];
            }

            $insertValues = array(
                'id' => '',
                'start' => $this->date->toSQL($formData['start']),
                'end' => $this->date->toSQL($formData['end']),
                'category_id' => strlen($formData['cat_create']) >= 3 ? $this->get('categories.helpers')->categoriesCreate($formData['cat_create'], 'files') : $formData['cat'],
                'file' => $newFile,
                'size' => $filesize,
                'title' => Core\Functions::strEncode($formData['title']),
                'text' => Core\Functions::strEncode($formData['text'], true),
                'comments' => $settings['comments'] == 1 && isset($formData['comments']) ? 1 : 0,
                'user_id' => $this->auth->getUserId(),
            );


            $lastId = $this->filesModel->insert($insertValues);

            $this->aliases->insertUriAlias(
                sprintf(Files\Helpers::URL_KEY_PATTERN, $lastId),
                $formData['alias'],
                $formData['seo_keywords'],
                $formData['seo_description'],
                (int)$formData['seo_robots']);
            $this->seo->setCache();

            $this->secureHelper->unsetFormToken($this->request->query);

            $this->redirectMessages()->setMessage($lastId, $this->lang->t('system', $lastId !== false ? 'create_success' : 'create_error'), 'acp/files');
        } catch (Core\Exceptions\InvalidFormToken $e) {
            $this->redirectMessages()->setMessage(false, $e->getMessage(), 'acp/files');
        } catch (Core\Exceptions\ValidationFailed $e) {
            $this->view->assign('error_msg', $this->get('core.helpers.alerts')->errorBox($e->getMessage()));
        }
    }

    private function _editPost(array $formData, array $settings, array $dl)
    {
        try {
            $file = array();
            if (isset($formData['external'])) {
                $file = $formData['file_external'];
            } elseif (!empty($_FILES['file_internal']['name'])) {
                $file = array();
                $file['tmp_name'] = $_FILES['file_internal']['tmp_name'];
                $file['name'] = $_FILES['file_internal']['name'];
                $file['size'] = $_FILES['file_internal']['size'];
            }

            $validator = $this->get('files.validator');
            $validator->validateEdit($formData, $file);

            $updateValues = array(
                'start' => $this->date->toSQL($formData['start']),
                'end' => $this->date->toSQL($formData['end']),
                'category_id' => strlen($formData['cat_create']) >= 3 ? $this->get('categories.helpers')->categoriesCreate($formData['cat_create'], 'files') : $formData['cat'],
                'title' => Core\Functions::strEncode($formData['title']),
                'text' => Core\Functions::strEncode($formData['text'], true),
                'comments' => $settings['comments'] == 1 && isset($formData['comments']) ? 1 : 0,
                'user_id' => $this->auth->getUserId(),
            );

            // Falls eine neue Datei angegeben wurde, Änderungen durchführen
            if (isset($file)) {
                $upload = new Core\Helpers\Upload('files');

                if (is_array($file) === true) {
                    $result = $upload->moveFile($file['tmp_name'], $file['name']);
                    $newFile = $result['name'];
                    $filesize = $result['size'];
                } else {
                    $formData['filesize'] = (float)$formData['filesize'];
                    $newFile = $file;
                    $filesize = $formData['filesize'] . ' ' . $formData['unit'];
                }
                // SQL Query für die Änderungen
                $newFileSql = array(
                    'file' => $newFile,
                    'size' => $filesize,
                );

                $upload->removeUploadedFile($dl['file']);

                $updateValues = array_merge($updateValues, $newFileSql);
            }

            $bool = $this->filesModel->update($updateValues, $this->request->id);

            $this->aliases->insertUriAlias(
                sprintf(Files\Helpers::URL_KEY_PATTERN, $this->request->id),
                $formData['alias'],
                $formData['seo_keywords'],
                $formData['seo_description'],
                (int)$formData['seo_robots']
            );
            $this->seo->setCache();

            $this->filesCache->setCache($this->request->id);

            $this->secureHelper->unsetFormToken($this->request->query);

            $this->redirectMessages()->setMessage($bool, $this->lang->t('system', $bool !== false ? 'edit_success' : 'edit_error'), 'acp/files');
        } catch (Core\Exceptions\InvalidFormToken $e) {
            $this->redirectMessages()->setMessage(false, $e->getMessage(), 'acp/files');
        } catch (Core\Exceptions\ValidationFailed $e) {
            $this->view->assign('error_msg', $this->get('core.helpers.alerts')->errorBox($e->getMessage()));
        }
    }

    private function _settingsPost(array $formData)
    {
        try {
            $validator = $this->get('files.validator');
            $validator->validateSettings($formData);

            $data = array(
                'dateformat' => Core\Functions::strEncode($formData['dateformat']),
                'sidebar' => (int)$formData['sidebar'],
                'comments' => $formData['comments']
            );
            $bool = $this->filesConfig->setSettings($data);

            $this->secureHelper->unsetFormToken($this->request->query);

            $this->redirectMessages()->setMessage($bool, $this->lang->t('system', $bool === true ? 'settings_success' : 'settings_error'), 'acp/files');
        } catch (Core\Exceptions\InvalidFormToken $e) {
            $this->redirectMessages()->setMessage(false, $e->getMessage(), 'acp/files');
        } catch (Core\Exceptions\ValidationFailed $e) {
            $this->view->assign('error_msg', $this->get('core.helpers.alerts')->errorBox($e->getMessage()));
        }
    }

}