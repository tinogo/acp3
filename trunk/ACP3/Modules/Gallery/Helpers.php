<?php

/**
 * Gallery
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

namespace ACP3\Modules\Gallery;

use ACP3\Core;

abstract class Helpers
{
    /**
     *
     * @var Model
     */
    protected static $model;

    /**
     * @var Core\URI
     */
    protected static $uri;

    /**
     * @var Core\SEO
     */
    protected static $seo;

    protected static function _init()
    {
        if (!self::$model) {
            self::$uri = Core\Registry::get('URI');
            self::$seo = Core\Registry::get('SEO');
            self::$model = new Model(Core\Registry::get('Db'), Core\Registry::get('Lang'), Core\Registry::get('URI'));
        }
    }

    /**
     * Setzt einen einzelnen Alias für ein Bild einer Fotogalerie
     *
     * @param integer $pictureId
     * @return boolean
     */
    public static function generatePictureAlias($pictureId)
    {
        self::_init();

        $galleryId = self::$model->getGalleryIdFromPictureId($pictureId);
        $alias = self::$uri->getUriAlias('gallery/index/pics/id_' . $galleryId, true);
        if (!empty($alias)) {
            $alias .= '/img-' . $pictureId;
        }
        $seoKeywords = self::$seo->getKeywords('gallery/index/pics/id_' . $galleryId);
        $seoDescription = self::$seo->getDescription('gallery/index/pics/id_' . $galleryId);

        return self::$uri->insertUriAlias('gallery/index/details/id_' . $pictureId, $alias, $seoKeywords, $seoDescription);
    }

    /**
     * Setzt alle Bild-Aliase einer Fotogalerie neu
     *
     * @param integer $galleryId
     * @return boolean
     */
    public static function generatePictureAliases($galleryId)
    {
        self::_init();

        $pictures = self::$model->getPicturesByGalleryId($galleryId);
        $c_pictures = count($pictures);

        $alias = self::$seo->getUriAlias('gallery/index/pics/id_' . $galleryId, true);
        if (!empty($alias)) {
            $alias .= '/img';
        }
        $seo_keywords = self::$seo->getKeywords('gallery/index/pics/id_' . $galleryId);
        $seo_description = self::$seo->getDescription('gallery/index/pics/id_' . $galleryId);

        for ($i = 0; $i < $c_pictures; ++$i) {
            self::$uri->insertUriAlias('gallery/index/details/id_' . $pictures[$i]['id'], !empty($alias) ? $alias . '-' . $pictures[$i]['id'] : '', $seo_keywords, $seo_description);
        }

        return true;
    }

    /**
     * Sorgt dafür, dass wenn eine Fotogalerie gelöscht wird,
     * auch alle Bild-Aliase gelöscht werden
     *
     * @param integer $galleryId
     * @return boolean
     */
    public static function deletePictureAliases($galleryId)
    {
        self::_init();

        $pictures = self::$model->getPicturesByGalleryId($galleryId);
        $c_pictures = count($pictures);

        for ($i = 0; $i < $c_pictures; ++$i) {
            self::$uri->deleteUriAlias('gallery/index/details/id_' . $pictures[$i]['id']);
        }

        return true;
    }

    /**
     * Löscht ein Bild aus dem Dateisystem
     *
     * @param string $file
     */
    public static function removePicture($file)
    {
        Core\Functions::removeUploadedFile('cache/images', 'gallery_thumb_' . $file);
        Core\Functions::removeUploadedFile('cache/images', 'gallery_' . $file);
        Core\Functions::removeUploadedFile('gallery', $file);
    }

}