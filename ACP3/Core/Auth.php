<?php
namespace ACP3\Core;

use ACP3\Core\Helpers\Secure;
use ACP3\Modules\Users;

/**
 * Authenticates the user
 *
 * @author Tino Goratsch
 */
class Auth
{
    /**
     * Name des Authentifizierungscookies
     */
    const COOKIE_NAME = 'ACP3_AUTH';
    /**
     * Eingeloggter Benutzer oder nicht
     *
     * @var boolean
     */
    protected $isUser = false;
    /**
     * ID des Benutzers
     *
     * @var integer
     */
    protected $userId = 0;
    /**
     * Super User oder nicht
     *
     * @var boolean
     */
    protected $superUser = false;
    /**
     * Anzuzeigende Datensätze  pro Seite
     *
     * @var integer
     */
    public $entries = CONFIG_ENTRIES;
    /**
     * Standardsprache des Benutzers
     *
     * @var string
     */
    public $language = CONFIG_LANG;
    /**
     * @var array
     */
    protected $userInfo = array();
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var \ACP3\Modules\Users\Model
     */
    protected $usersModel;
    /**
     * @var Secure
     */
    protected $secureHelper;

    /**
     * Findet heraus, falls der ACP3_AUTH Cookie gesetzt ist, ob der
     * Seitenbesucher auch wirklich ein registrierter Benutzer des ACP3 ist
     */
    function __construct(
        \Doctrine\DBAL\Connection $db,
        Session $session,
        Secure $secureHelper)
    {
        $this->session = $session;
        $this->usersModel = new Users\Model($db);
        $this->secureHelper = $secureHelper;

        if (isset($_COOKIE[self::COOKIE_NAME])) {
            $cookie = base64_decode($_COOKIE[self::COOKIE_NAME]);
            $cookieData = explode('|', $cookie);

            $user = $this->usersModel->getOneActiveUserByNickname($cookieData[0]);
            if (!empty($user)) {
                $dbPassword = substr($user['pwd'], 0, 40);
                if ($dbPassword === $cookieData[1]) {
                    $this->isUser = true;
                    $this->userId = (int)$user['id'];
                    $this->superUser = (bool)$user['super_user'];

                    $config = new Config($db, 'users');
                    $settings = $config->getSettings();
                    $this->entries = $settings['entries_override'] == 1 && $user['entries'] > 0 ? (int)$user['entries'] : (int)CONFIG_ENTRIES;
                    $this->language = $settings['language_override'] == 1 ? $user['language'] : CONFIG_LANG;
                }
            } else {
                $this->logout();
            }
        }
    }

    /**
     * Gibt die UserId des eingeloggten Benutzers zurück
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Gibt ein Array mit den angeforderten Daten eines Benutzers zurück
     *
     * @param int $userId
     *    Der angeforderte Benutzer
     * @return mixed
     */
    public function getUserInfo($userId = 0)
    {
        if (empty($userId) && $this->isUser() === true) {
            $userId = $this->getUserId();
        }

        $userId = (int)$userId;

        if (empty($this->userInfo[$userId])) {
            $countries = Lang::worldCountries();
            $info = $this->usersModel->getOneById($userId);
            if (!empty($info)) {
                $info['country_formatted'] = !empty($info['country']) && isset($countries[$info['country']]) ? $countries[$info['country']] : '';
                $this->userInfo[$userId] = $info;
            }
        }

        return !empty($this->userInfo[$userId]) ? $this->userInfo[$userId] : false;
    }

    /**
     * Gibt die eingestellte Standardsprache des Benutzers aus
     *
     * @return string
     */
    public function getUserLanguage()
    {
        return $this->language;
    }

    /**
     * Gibt den Status von $isUser zurück
     *
     * @return boolean
     */
    public function isUser()
    {
        return $this->isUser === true && $this->getUserId() !== 0;
    }

    /**
     * Gibt aus, ob der aktuell eingeloggte Benutzer der Super User ist, oder nicht
     *
     * @return boolean
     */
    public function isSuperUser()
    {
        return $this->superUser;
    }

    /**
     * Loggt einen User ein
     *
     * @param string $username
     *    Der zu verwendente Username
     * @param string $password
     *    Das zu verwendente Passwort
     * @param integer $expiry
     *    Gibt die Zeit in Sekunden an, wie lange der User eingeloggt bleiben soll
     * @return integer
     */
    public function login($username, $password, $expiry)
    {
        $user = $this->usersModel->getOneByNickname($username);

        if (!empty($user)) {
            // Useraccount ist gesperrt
            if ($user['login_errors'] >= 3) {
                return -1;
            }

            // Passwort aus Datenbank
            $dbHash = substr($user['pwd'], 0, 40);

            // Hash für eingegebenes Passwort generieren
            $salt = substr($user['pwd'], 41, 53);

            $formPasswordHash = $this->secureHelper->generateSaltedPassword($salt, $password);

            // Wenn beide Hashwerte gleich sind, Benutzer authentifizieren
            if ($dbHash === $formPasswordHash) {
                // Login-Fehler zurücksetzen
                if ($user['login_errors'] > 0) {
                    $this->usersModel->update(array('login_errors' => 0), (int)$user['id']);
                }

                $this->setCookie($username, $dbHash, $expiry);

                // Neue Session-ID generieren
                Session::secureSession(true);

                $this->isUser = true;
                $this->userId = (int)$user['id'];

                return 1;
            } else { // Beim dritten falschen Login den Account sperren
                $loginErrors = $user['login_errors'] + 1;
                $this->usersModel->update(array('login_errors' => $loginErrors), (int)$user['id']);
                if ($loginErrors === 3) {
                    return -1;
                }
            }
        }
        return 0;
    }

    /**
     * Loggt einen User aus
     *
     * @return boolean
     */
    public function logout()
    {
        $this->session->session_destroy(session_id());
        return $this->setCookie('', '', -50400);
    }

    /**
     * Setzt den internen Authentifizierungscookie
     *
     * @param string $nickname
     *  Der Loginname des Users
     * @param string $password
     *  Die Hashsumme des Passwortes
     * @param integer $expiry
     *  Zeit in Sekunden, bis der Cookie seine Gültigkeit verliert
     * @return bool
     */
    public function setCookie($nickname, $password, $expiry)
    {
        $value = base64_encode($nickname . '|' . $password);
        $expiry = time() + $expiry;
        $domain = strpos($_SERVER['HTTP_HOST'], '.') !== false ? $_SERVER['HTTP_HOST'] : '';
        return setcookie(self::COOKIE_NAME, $value, $expiry, ROOT_DIR, $domain);
    }
}