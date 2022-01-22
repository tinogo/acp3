<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Wysiwygckeditor\WYSIWYG\Editor;

use ACP3\Core;
use ACP3\Core\Component\ComponentRegistry;
use ACP3\Core\WYSIWYG\Editor\Textarea;
use ACP3\Modules\ACP3\Emoticons\Repository\EmoticonRepository;
use ACP3\Modules\ACP3\Filemanager\Helpers;

/**
 * Implementation of the AbstractWYSIWYG class for CKEditor.
 */
class CKEditor extends Textarea
{
    private bool $isInitialized = false;

    public function __construct(private Core\ACL $acl, private Core\Assets\IncludeJs $includeJs, private Core\Modules $modules, private Core\I18n\Translator $translator, private Core\Environment\ApplicationPath $appPath, private ?EmoticonRepository $emoticonRepository = null, private ?Helpers $filemanagerHelpers = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFriendlyName(): string
    {
        return 'CKEditor';
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $params = []): void
    {
        parent::setParameters($params);

        $this->config['toolbar'] = (isset($params['toolbar']) && $params['toolbar'] === 'simple') ? 'Basic' : 'Full';
        $this->config['height'] = ($params['height'] ?? 250) . 'px';
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        $wysiwyg = [
            'friendly_name' => $this->getFriendlyName(),
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
            'js' => $this->init(),
            'advanced' => $this->advanced,
            'required' => $this->required,
            'data_config' => $this->configure(),
        ];

        if ($wysiwyg['advanced'] === true) {
            $wysiwyg['advanced_replace_content'] = 'CKEDITOR.instances.' . $wysiwyg['id'] . '.insertHtml(text);';
        }

        return ['wysiwyg' => $wysiwyg];
    }

    /**
     * Configures the CKEditor instance.
     */
    private function configure(): string
    {
        $this->config['entities'] = false;
        $this->config['extraPlugins'] = 'divarea,embed,codemirror';
        $this->config['allowedContent'] = true;
        $this->config['embed_provider'] = '//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}';
        $this->config['language'] = $this->translator->getShortIsoCode();
        $this->config['codemirror'] = [
            'theme' => 'default',
            'lineNumbers' => true,
            'lineWrapping' => true,
            'matchBrackets' => true,
            'autoCloseTags' => true,
            'autoCloseBrackets' => true,
            'enableSearchTools' => true,
            'enableCodeFolding' => true,
            'enableCodeFormatting' => true,
            'autoFormatOnStart' => true,
            'autoFormatOnUncomment' => true,
            'highlightActiveLine' => true,
            'highlightMatches' => true,
            'showFormatButton' => false,
            'showCommentButton' => false,
            'showUncommentButton' => false,
        ];

        // Full toolbar
        if (!isset($this->config['toolbar']) || $this->config['toolbar'] !== 'Basic') {
            $this->configureFullToolbar();
        } else { // basic toolbar
            $this->configureBasicToolbar();
        }

        return json_encode($this->config, JSON_THROW_ON_ERROR);
    }

    /**
     * Prints javascript code.
     */
    private function script(string $js): string
    {
        $out = '<script type="text/javascript">';
        $out .= $js;
        $out .= "</script>\n";

        return $out;
    }

    private function init(): string
    {
        if ($this->isInitialized === true) {
            return '';
        }

        $this->isInitialized = true;
        $basePath = $this->appPath->getWebRoot() . 'vendor/ckeditor/ckeditor/';
        $out = '';

        // Skip relative paths...
        if (!str_starts_with($basePath, '..')) {
            $out .= $this->script("window.CKEDITOR_BASEPATH='" . $basePath . "';");
        }

        $out .= '<script type="text/javascript" src="' . $basePath . "ckeditor.js\"></script>\n";

        // Add custom plugins
        $path = ComponentRegistry::getPathByName('wysiwygckeditor');

        $ckeditorPluginsDir = str_replace(
            '\\',
            '/',
            $this->appPath->getWebRoot()
            . substr($path, \strlen(ACP3_ROOT_DIR . DIRECTORY_SEPARATOR))
            . '/Resources/Assets/js/ckeditor/plugins/'
        );

        $js = "CKEDITOR.plugins.addExternal('codemirror', '" . $ckeditorPluginsDir . "codemirror/');\n";
        $js .= "CKEDITOR.plugins.addExternal('divarea', '" . $ckeditorPluginsDir . "divarea/');\n";
        $js .= "CKEDITOR.plugins.addExternal('embedbase', '" . $ckeditorPluginsDir . "embedbase/');\n";
        $js .= "CKEDITOR.plugins.addExternal('embed', '" . $ckeditorPluginsDir . "embed/');\n";
        $js .= 'CKEDITOR.dtd.$removeEmpty[\'i\'] = false;' . "\n";

        $out .= $this->script($js);
        $out .= $this->includeJs->add('Wysiwygckeditor', 'partials/ckeditor');

        return $out;
    }

    private function applyEmoticons(): void
    {
        $this->config['smiley_path'] = $this->appPath->getWebRoot() . 'uploads/emoticons/';
        $this->config['smiley_images'] = $this->config['smiley_descriptions'] = '';
        $emoticons = $this->emoticonRepository->getAll();

        $images = $descriptions = [];
        foreach ($emoticons as $i => $emoticon) {
            $images[] = $emoticons[$i]['img'];
            $descriptions[] = $emoticons[$i]['description'];
        }

        $this->config['smiley_images'] = $images;
        $this->config['smiley_descriptions'] = $descriptions;
    }

    private function addFileManager(): void
    {
        if ($this->filemanagerHelpers === null) {
            return;
        }
        if (!$this->acl->hasPermission('admin/filemanager/index/richfilemanager')) {
            return;
        }

        $this->config['filebrowserBrowseUrl'] = $this->filemanagerHelpers->getFilemanagerPath();
    }

    private function configureFullToolbar(): void
    {
        $this->config['extraPlugins'] = 'codemirror,divarea,embedbase,embed';

        $this->addFileManager();

        // Toolbar configuration
        $this->config['toolbarGroups'] = [
            ['name' => 'document', 'groups' => ['mode', 'document', 'doctools']],
            ['name' => 'clipboard', 'groups' => ['clipboard', 'undo']],
            ['name' => 'editing', 'groups' => ['find', 'selection', 'spellchecker']],
            ['name' => 'forms'],
            '/',
            ['name' => 'basicstyles', 'groups' => ['basicstyles', 'cleanup']],
            ['name' => 'paragraph', 'groups' => ['list', 'indent', 'blocks', 'align', 'bidi']],
            ['name' => 'links'],
            ['name' => 'insert'],
            '/',
            ['name' => 'styles'],
            ['name' => 'colors'],
            ['name' => 'tools'],
            ['name' => 'others'],
            ['name' => 'about'],
        ];

        // Include emoticons, if available
        if ($this->modules->isInstalled('emoticons') === true) {
            $this->applyEmoticons();
        }
    }

    private function configureBasicToolbar(): void
    {
        $this->config['extraPlugins'] = 'divarea,codemirror';
        $this->config['toolbar'] = [
            [
                'Source',
                '-',
                'Undo',
                'Redo',
                '-',
                'Bold',
                'Italic',
                '-',
                'NumberedList',
                'BulletedList',
                '-',
                'Link',
                'Unlink',
                '-',
                'About',
            ],
        ];
    }
}
