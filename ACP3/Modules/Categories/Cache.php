<?php
namespace ACP3\Modules\Categories;

use ACP3\Core;

class Cache
{
    /**
     * @var Model
     */
    protected $categoriesModel;
    /**
     * @var \ACP3\Core\Cache
     */
    protected $cache;

    public function __construct(Model $categoriesModel)
    {
        $this->categoriesModel = $categoriesModel;
        $this->cache = new Core\Cache('categories');
    }

    /**
     * Erstellt den Cache für die Kategorien eines Moduls
     *
     * @param string $moduleName
     *  Das Modul, für welches der Kategorien-Cache erstellt werden soll
     * @return boolean
     */
    public function setCache($moduleName)
    {
        return $this->cache->save($moduleName, $this->categoriesModel->getAllByModuleName($moduleName));
    }

    /**
     * Gibt die gecacheten Kategorien des jeweiligen Moduls zurück
     *
     * @param string $moduleName
     *  Das jeweilige Modul, für welches die Kategorien geholt werden sollen
     * @return array
     */
    public function getCache($moduleName)
    {
        if ($this->cache->contains($moduleName) === false) {
            $this->setCache($moduleName);
        }

        return $this->cache->fetch($moduleName);
    }

}