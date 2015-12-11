<?php
namespace ACP3\Core\I18n;

use ACP3\Core\Config;
use ACP3\Core\I18n\DictionaryCache as LanguageCache;
use ACP3\Core\User;

/**
 * Class Translator
 * @package ACP3\Core\I18n
 */
class Translator
{
    /**
     * @var \ACP3\Core\User
     */
    protected $user;
    /**
     * @var \ACP3\Core\I18n\DictionaryCache
     */
    protected $cache;
    /**
     * @var \ACP3\Core\Config
     */
    protected $config;
    /**
     * Die zur Zeit eingestellte Sprache
     *
     * @var string
     */
    protected $locale = '';
    /**
     * @var string
     */
    protected $lang2Characters = '';
    /**
     * @var array
     */
    protected $languagePacks = [];
    /**
     * @var array
     */
    protected $buffer = [];

    /**
     * @param \ACP3\Core\User                 $user
     * @param \ACP3\Core\I18n\DictionaryCache $cache
     * @param \ACP3\Core\Config               $config
     */
    public function __construct(
        User $user,
        LanguageCache $cache,
        Config $config
    )
    {
        $this->user = $user;
        $this->cache = $cache;
        $this->config = $config;
    }

    /**
     * Überprüft, ob das angegebene Sprachpaket existiert
     *
     * @param string $locale
     *
     * @return boolean
     */
    public static function languagePackExists($locale)
    {
        return !preg_match('=/=',
            $locale) && is_file(MODULES_DIR . 'ACP3/System/Resources/i18n/' . $locale . '.xml') === true;
    }

    /**
     * Gibt die aktuell eingestellte Sprache zurück
     *
     * @return string
     */
    public function getLanguage()
    {
        if ($this->locale === '') {
            $locale = $this->user->getLanguage();
            $this->locale = self::languagePackExists($locale) === true ? $locale : $this->config->getSettings('system')['lang'];
        }

        return $this->locale;
    }

    /**
     * @return string
     */
    public function getShortIsoCode()
    {
        return substr($this->getLanguage(), 0, strpos($this->getLanguage(), '_'));
    }

    /**
     * Verändert die aktuell eingestellte Sprache
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLanguage($locale)
    {
        if (self::languagePackExists($locale) === true) {
            $this->locale = $locale;
        }

        return $this;
    }

    /**
     * Gets the writing direction of the language
     *
     * @return string
     */
    public function getDirection()
    {
        if (isset($this->buffer[$this->getLanguage()]) === false) {
            $this->buffer[$this->getLanguage()] = $this->cache->getLanguageCache($this->getLanguage());
        }

        return isset($this->buffer[$this->getLanguage()]['info']['direction']) ? $this->buffer[$this->getLanguage()]['info']['direction'] : 'ltr';
    }

    /**
     * Gibt den angeforderten Sprachstring aus
     *
     * @param string $module
     * @param string $key
     * @param array  $arguments
     *
     * @return string
     */
    public function t($module, $key, array $arguments = [])
    {
        if (isset($this->buffer[$this->getLanguage()]) === false) {
            $this->buffer[$this->getLanguage()] = $this->cache->getLanguageCache($this->getLanguage());
        }

        if (isset($this->buffer[$this->getLanguage()]['keys'][$module . $key])) {
            return strtr($this->buffer[$this->getLanguage()]['keys'][$module . $key], $arguments);
        }

        return strtoupper('{' . $module . '_' . $key . '}');
    }

    /**
     * Gets all currently available languages
     *
     * @param string $currentLanguage
     *
     * @return array
     */
    public function getLanguagePack($currentLanguage)
    {
        if (empty($this->languagePacks)) {
            $this->languagePacks = $this->cache->getLanguagePacksCache();
        }

        $languages = $this->languagePacks;

        foreach ($languages as $key => $value) {
            $languages[$key]['selected'] = $languages[$key]['iso'] === $currentLanguage;
        }

        return $languages;
    }

}
