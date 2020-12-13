<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Assets\Minifier;

class JavaScript extends AbstractMinifier
{
    /**
     * @var array
     */
    protected $javascript = [];

    protected function getAssetGroup(): string
    {
        return 'js';
    }

    protected function getFileExtension(): string
    {
        return 'js';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    protected function processLibraries(string $layout): array
    {
        $cacheId = $this->buildCacheId($layout);

        if ($this->systemCache->contains($cacheId) === false) {
            $this->fetchLibraries();
            $this->fetchThemeJavaScript($layout);

            $this->systemCache->save($cacheId, $this->javascript);
        }

        return $this->systemCache->fetch($cacheId);
    }

    /**
     * Fetches the javascript files of all enabled frontend frameworks/libraries.
     *
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    protected function fetchLibraries(): void
    {
        foreach ($this->assets->getLibraries() as $library) {
            if ($library->isEnabled() === false || !$library->getJs()) {
                continue;
            }

            foreach ($library->getJs() as $javascript) {
                $this->javascript[] = $this->fileResolver->getStaticAssetPath(
                    $library->getModuleName() ?: static::SYSTEM_MODULE_NAME,
                    static::ASSETS_PATH_JS,
                    $javascript
                );
            }
        }
    }

    /**
     * Fetches the theme javascript files.
     */
    protected function fetchThemeJavaScript(string $layout): void
    {
        foreach ($this->assets->fetchAdditionalThemeJsFiles() as $file) {
            $this->javascript[] = $this->fileResolver->getStaticAssetPath('', static::ASSETS_PATH_JS, $file);
        }

        // Include general js file of the layout
        $this->javascript[] = $this->fileResolver->getStaticAssetPath('', static::ASSETS_PATH_JS, $layout . '.js');
    }
}
