<?php
namespace ACP3\Core;
use ACP3\Core\Router\Aliases;

/**
 * Class SEO
 * @package ACP3\Core
 */
class SEO
{
    /**
     * @var Cache2
     */
    protected $cache;
    /**
     * @var Router\Aliases
     */
    protected $aliases;
    /**
     * @var Lang
     */
    protected $db;
    /**
     * @var Lang
     */
    protected $lang;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var View
     */
    protected $view;
    /**
     * Gibt die nächste Seite an
     *
     * @var string
     */
    protected $nextPage = '';
    /**
     * Gibt die vorherige Seite an
     *
     * @var string
     */
    protected $previousPage = '';
    /**
     * Kanonische URL
     *
     * @var string
     */
    protected $canonical = '';
    /**
     * @var array
     */
    protected $aliasCache = array();

    protected $metaDescriptionPostfix = '';

    public function __construct(
        \Doctrine\DBAL\Connection $db,
        Lang $lang,
        Request $request,
        Aliases $aliases,
        View $view)
    {
        $this->cache = new Cache2('seo');
        $this->db = $db;
        $this->lang = $lang;
        $this->request = $request;
        $this->aliases = $aliases;
        $this->view = $view;

        $this->aliasCache = $this->getCache();
    }

    /**
     * Setzt den Cache für die URI-Aliase
     *
     * @return boolean
     */
    public function setCache()
    {
        $aliases = $this->db->fetchAll('SELECT uri, keywords, description, robots FROM ' . DB_PRE . 'seo WHERE keywords != "" OR description != "" OR robots != 0');
        $c_aliases = count($aliases);
        $data = array();

        for ($i = 0; $i < $c_aliases; ++$i) {
            $data[$aliases[$i]['uri']] = array(
                'keywords' => $aliases[$i]['keywords'],
                'description' => $aliases[$i]['description'],
                'robots' => $aliases[$i]['robots']
            );
        }

        return $this->cache->save('meta', $data);
    }

    /**
     * Gibt den Cache der URI-Aliase zurück
     *
     * @return array
     */
    public function getCache()
    {
        if ($this->cache->contains('meta') === false) {
            $this->setCache();
        }

        return $this->cache->fetch('meta');
    }

    /**
     * Gibt die für die jeweilige Seite gesetzten Metatags zurück
     *
     * @return string
     */
    public function getMetaTags()
    {
        $meta = array(
            'description' => $this->request->area === 'admin' ? '' : $this->getPageDescription(),
            'keywords' => $this->request->area === 'admin' ? '' : $this->getPageKeywords(),
            'robots' => $this->request->area === 'admin' ? 'noindex,nofollow' : $this->getPageRobotsSetting(),
            'previous_page' => $this->previousPage,
            'next_page' => $this->nextPage,
            'canonical' => $this->canonical,
        );
        $this->view->assign('meta', $meta);

        return $this->view->fetchTemplate('system/meta.tpl');
    }

    /**
     * Gibt die Beschreibung der aktuell angezeigten Seite zurück
     *
     * @return string
     */
    public function getPageDescription()
    {
        // Meta Description für die Homepage einer Website
        if ($this->request->query === CONFIG_HOMEPAGE) {
            return CONFIG_SEO_META_DESCRIPTION !== '' ? CONFIG_SEO_META_DESCRIPTION : '';
        } else {
            $description = $this->getDescription($this->request->getUriWithoutPages());
            if (empty($description)) {
                $description = $this->getDescription($this->request->mod . '/' . $this->request->controller . '/' . $this->request->file);
            }

            return $description . (!empty($description) && !empty($this->metaDescriptionPostfix) ? ' - ' . $this->metaDescriptionPostfix : '');
        }
    }

    /**
     * Gibt die Keywords der aktuell angezeigten Seite oder der
     * Elternseite zurück
     *
     * @return string
     */
    public function getPageKeywords()
    {
        $keywords = $this->getKeywords($this->request->getUriWithoutPages());
        if (empty($keywords)) {
            $keywords = $this->getKeywords($this->request->mod . '/' . $this->request->controller . '/' . $this->request->file);
        }
        if (empty($keywords)) {
            $keywords = $this->getKeywords($this->request->mod);
        }

        return strtolower(!empty($keywords) ? $keywords : CONFIG_SEO_META_KEYWORDS);
    }

    /**
     * Gibt den Robots-Metatag der aktuell angezeigten Seite oder der
     * Elternseite zurück
     *
     * @return string
     */
    public function getPageRobotsSetting()
    {
        $robots = $this->getRobotsSetting($this->request->getUriWithoutPages());
        if (empty($robots)) {
            $robots = $this->getRobotsSetting($this->request->mod . '/' . $this->request->controller . '/' . $this->request->file);
        }
        if (empty($robots)) {
            $robots = $this->getRobotsSetting($this->request->mod);
        }

        return strtolower(!empty($robots) ? $robots : $this->getRobotsSetting());
    }

    /**
     * Gibt die Beschreibung der Seite zurück
     *
     * @param string $path
     * @return string
     */
    public function getDescription($path)
    {
        $path .= !preg_match('/\/$/', $path) ? '/' : '';

        return !empty($this->aliasCache[$path]['description']) ? $this->aliasCache[$path]['description'] : '';
    }

    /**
     *
     * @param string $string
     */
    public function setDescriptionPostfix($string)
    {
        $this->metaDescriptionPostfix = $string;
    }

    /**
     * Gibt die Schlüsselwörter der Seite zurück
     *
     * @param string $path
     * @return string
     */
    public function getKeywords($path)
    {
        $path .= !preg_match('/\/$/', $path) ? '/' : '';

        return !empty($this->aliasCache[$path]['keywords']) ? $this->aliasCache[$path]['keywords'] : '';
    }

    /**
     * Gibt die jeweilige Einstellung für den Robots-Metatag zurück
     *
     * @param string $path
     * @return string
     */
    public function getRobotsSetting($path = '')
    {
        $replace = array(
            1 => 'index,follow',
            2 => 'index,nofollow',
            3 => 'noindex,follow',
            4 => 'noindex,nofollow',
        );

        if ($path === '') {
            return strtr(CONFIG_SEO_ROBOTS, $replace);
        } else {
            $path .= !preg_match('/\/$/', $path) ? '/' : '';

            $robot = isset($this->aliasCache[$path]) === false || $this->aliasCache[$path]['robots'] == 0 ? CONFIG_SEO_ROBOTS : $this->aliasCache[$path]['robots'];
            return strtr($robot, $replace);
        }
    }

    /**
     * Setzt die kanonische URI
     *
     * @param string $path
     */
    public function setCanonicalUri($path)
    {
        $this->canonical = $path;
    }

    /**
     * Setzt die nächste Seite
     *
     * @param string $path
     */
    public function setNextPage($path)
    {
        $this->nextPage = $path;
    }

    /**
     * Setzt die vorherige Seite
     *
     * @param string $path
     */
    public function setPreviousPage($path)
    {
        $this->previousPage = $path;
    }

    /**
     * Gibt die Formularfelder für die Suchmaschinenoptimierung aus
     *
     * @param string $path
     * @return string
     */
    public function formFields($path = '')
    {
        if (!empty($path)) {
            $path .= !preg_match('/\/$/', $path) ? '/' : '';

            $alias = isset($_POST['alias']) ? $_POST['alias'] : $this->aliases->getUriAlias($path, true);
            $keywords = isset($_POST['seo_keywords']) ? $_POST['seo_keywords'] : $this->getKeywords($path);
            $description = isset($_POST['seo_description']) ? $_POST['seo_description'] : $this->getDescription($path);
            $robots = isset($this->aliasCache[$path]) === true ? $this->aliasCache[$path]['robots'] : 0;
        } else {
            $alias = $keywords = $description = '';
            $robots = 0;
        }

        $langRobots = array(
            sprintf($this->lang->t('system', 'seo_robots_use_system_default'), $this->getRobotsSetting()),
            $this->lang->t('system', 'seo_robots_index_follow'),
            $this->lang->t('system', 'seo_robots_index_nofollow'),
            $this->lang->t('system', 'seo_robots_noindex_follow'),
            $this->lang->t('system', 'seo_robots_noindex_nofollow')
        );
        $seo = array(
            'alias' => $alias,
            'keywords' => $keywords,
            'description' => $description,
            'robots' => Functions::selectGenerator('seo_robots', array(0, 1, 2, 3, 4), $langRobots, $robots)
        );

        $this->view->assign('seo', $seo);
        return $this->view->fetchTemplate('system/seo_fields.tpl');
    }

}