<?php

namespace ACP3\Modules\ACP3\Gallery\Installer;

use ACP3\Core\Modules;
use ACP3\Modules\ACP3\System;
use ACP3\Modules\ACP3\Permissions;

/**
 * Class Schema
 * @package ACP3\Modules\ACP3\Gallery\Installer
 */
class Schema implements Modules\Installer\SchemaInterface
{
    /**
     * @return array
     */
    public function specialResources()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getModuleName()
    {
        return 'gallery';
    }

    /**
     * @return int
     */
    public function getSchemaVersion()
    {
        return 36;
    }

    /**
     * @return array
     */
    public function createTables()
    {
        return [
            "CREATE TABLE `{pre}gallery` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `start` DATETIME NOT NULL,
                `end` DATETIME NOT NULL,
                `title` VARCHAR(120) NOT NULL,
                `user_id` INT UNSIGNED NOT NULL,
                PRIMARY KEY (`id`)
            ) {ENGINE} {CHARSET};",
            "CREATE TABLE `{pre}gallery_pictures` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `pic` INT(10) UNSIGNED NOT NULL,
                `gallery_id` INT(10) UNSIGNED NOT NULL,
                `file` VARCHAR(120) NOT NULL,
                `description` TEXT NOT NULL,
                `comments` TINYINT(1) UNSIGNED NOT NULL,
                PRIMARY KEY (`id`), INDEX `foreign_gallery_id` (`gallery_id`)
            ) {ENGINE} {CHARSET};"
        ];
    }

    /**
     * @return array
     */
    public function removeTables()
    {
        return [
            "DROP TABLE `{pre}gallery_pictures`;",
            "DROP TABLE `{pre}gallery`;"
        ];
    }

    /**
     * @return array
     */
    public function settings()
    {
        return [
            'width' => 640,
            'height' => 480,
            'thumbwidth' => 160,
            'thumbheight' => 120,
            'maxwidth' => 2048,
            'maxheight' => 1536,
            'filesize' => 20971520,
            'overlay' => 1,
            'comments' => 1,
            'dateformat' => 'long',
            'sidebar' => 5,
        ];
    }
}
