<?php

namespace ACP3\Modules\ACP3\WYSIWYGTinyMCE\WYSIWYG;

use ACP3\Core;

/**
 * Implementation of the AbstractWYSIWYG class for TinyMCE
 * @package ACP3\Modules\ACP3\WYSIWYGTinyMCE\WYSIWYG
 */
class TinyMCE extends Core\WYSIWYG\Textarea
{
    /**
     * @var \ACP3\Core\Assets\MinifierInterface
     */
    protected $minifier;
    /**
     * @var \ACP3\Core\I18n\Translator
     */
    protected $translator;
    /**
     * @var \ACP3\Core\View
     */
    protected $view;
    /**
     * @var \ACP3\Core\Environment\ApplicationPath
     */
    protected $appPath;
    /**
     * @var \ACP3\Modules\ACP3\Filemanager\Helpers
     */
    protected $filemanagerHelpers;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @param \ACP3\Core\Assets\MinifierInterface    $minifier
     * @param \ACP3\Core\I18n\Translator             $translator
     * @param \ACP3\Core\View                        $view
     * @param \ACP3\Core\Environment\ApplicationPath $appPath
     */
    public function __construct(
        Core\Assets\MinifierInterface $minifier,
        Core\I18n\Translator $translator,
        Core\View $view,
        Core\Environment\ApplicationPath $appPath
    )
    {
        $this->minifier = $minifier;
        $this->translator = $translator;
        $this->view = $view;
        $this->appPath = $appPath;
    }

    /**
     * @inheritdoc
     */
    public function getFriendlyName()
    {
        return 'TinyMCE';
    }

    /**
     * @param \ACP3\Modules\ACP3\Filemanager\Helpers $filemanagerHelpers
     *
     * @return $this
     */
    public function setFilemanagerHelpers(\ACP3\Modules\ACP3\Filemanager\Helpers $filemanagerHelpers)
    {
        $this->filemanagerHelpers = $filemanagerHelpers;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setParameters(array $params = [])
    {
        parent::setParameters($params);

        $this->config['toolbar'] = (isset($params['toolbar'])) ? $params['toolbar'] : '';
        $this->config['height'] = ((isset($params['height'])) ? $params['height'] : 250) . 'px';
    }

    /**
     * @inheritdoc
     */
    public function display()
    {
        $editor = '';

        if ($this->initialized === false) {
            $this->initialized = true;

            $editor .= '<script type="text/javascript" src="' . $this->appPath->getWebRoot() . 'vendor/tinymce/tinymce/tinymce.min.js"></script>';
        }

        $editor .= $this->configure();

        $wysiwyg = [
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
            'js' => $editor,
            'advanced' => $this->advanced,
        ];

        if ($wysiwyg['advanced'] === true) {
            $wysiwyg['advanced_replace_content'] = 'tinyMCE.execInstanceCommand(\'' . $this->id . '\',"mceInsertContent",false,text);';
        }

        $this->view->assign('wysiwyg', $wysiwyg);
        return $this->view->fetchTemplate('system/wysiwyg.tpl');
    }

    /**
     * @return string
     */
    private function configure()
    {

        $config = [
            'selector' => 'textarea#' . $this->id,
            'theme' => 'modern',
            'height' => $this->config['height'],
            'content_css' => $this->minifier->getURI()
        ];

        // Basic editor
        if (isset($this->config['toolbar']) && $this->config['toolbar'] === 'simple') {
            $plugins = [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table contextmenu paste'
            ];
            $toolbar = 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image';
            $imagesAdvanced = 'false';
        } else { // Full editor
            $plugins = [
                'advlist autolink lists link image charmap print preview hr anchor pagebreak',
                'searchreplace wordcount visualblocks visualchars code fullscreen',
                'insertdatetime media nonbreaking save table contextmenu directionality',
                'emoticons template paste textcolor colorpicker'
            ];
            $toolbar = 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media | forecolor backcolor emoticons';
            $imagesAdvanced = 'true';

            if ($this->filemanagerHelpers instanceof \ACP3\Modules\ACP3\Filemanager\Helpers) {
                $this->view->assign('filemanager_path', $this->filemanagerHelpers->getFilemanagerPath());
            }
        }


        $this->view->assign('tinymce_config', $config);
        $this->view->assign('plugins', json_encode($plugins));
        $this->view->assign('toolbar', $toolbar);
        $this->view->assign('image_advtab', $imagesAdvanced);

        return $this->view->fetchTemplate('WYSIWYGTinyMCE/tinymce.tpl');
    }
}
