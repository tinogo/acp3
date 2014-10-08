<?php

namespace ACP3\Modules\Contact\Controller;

use ACP3\Core;
use ACP3\Modules\Contact;

/**
 * Class Index
 * @package ACP3\Modules\Contact\Controller
 */
class Index extends Core\Modules\Controller\Frontend
{
    /**
     * @var Core\Helpers\Secure
     */
    protected $secureHelper;
    /**
     * @var \ACP3\Core\Config
     */
    protected $contactConfig;

    public function __construct(
        Core\Context\Frontend $context,
        Core\Helpers\Secure $secureHelper,
        Core\Config $contactConfig)
    {
        parent::__construct($context);

        $this->secureHelper = $secureHelper;
        $this->contactConfig = $contactConfig;
    }

    public function actionIndex()
    {
        if (empty($_POST) === false) {
            try {
                $validator = $this->get('contact.validator');
                $validator->validate($_POST);

                $settings = $this->contactConfig->getSettings();
                $_POST['message'] = Core\Functions::strEncode($_POST['message'], true);

                $subject = sprintf($this->lang->t('contact', 'contact_subject'), CONFIG_SEO_TITLE);
                $body = str_replace(array('{name}', '{mail}', '{message}', '\n'), array($_POST['name'], $_POST['mail'], $_POST['message'], "\n"), $this->lang->t('contact', 'contact_body'));
                $bool = $this->get('core.functions')->generateEmail('', $settings['mail'], $_POST['mail'], $subject, $body);

                // Nachrichtenkopie an Absender senden
                if (isset($_POST['copy'])) {
                    $subjectCopy = sprintf($this->lang->t('contact', 'sender_subject'), CONFIG_SEO_TITLE);
                    $bodyCopy = sprintf($this->lang->t('contact', 'sender_body'), CONFIG_SEO_TITLE, $_POST['message']);
                    $this->get('core.functions')->generateEmail($_POST['name'], $_POST['mail'], $settings['mail'], $subjectCopy, $bodyCopy);
                }

                $this->secureHelper->unsetFormToken($this->request->query);

                $this->setContent($this->get('core.helpers.alerts')->confirmBox($bool === true ? $this->lang->t('contact', 'send_mail_success') : $this->lang->t('contact', 'send_mail_error'), $this->router->route('contact')));
                return;
            } catch (Core\Exceptions\InvalidFormToken $e) {
                $this->redirectMessages()->setMessage(false, $e->getMessage(), 'contact');
            } catch (Core\Exceptions\ValidationFailed $e) {
                $this->view->assign('error_msg', $this->get('core.helpers.alerts')->errorBox($e->getMessage()));
            }
        }

        $this->redirectMessages()->getMessage();

        $defaults = array(
            'name' => '',
            'name_disabled' => '',
            'mail' => '',
            'mail_disabled' => '',
            'message' => '',
        );

        // Falls Benutzer eingeloggt ist, Formular schon teilweise ausfüllen
        if ($this->auth->isUser() === true) {
            $user = $this->auth->getUserInfo();
            $disabled = ' readonly="readonly" class="readonly"';
            $defaults['name'] = !empty($user['realname']) ? $user['realname'] : $user['nickname'];
            $defaults['name_disabled'] = $disabled;
            $defaults['mail'] = $user['mail'];
            $defaults['mail_disabled'] = $disabled;
        }

        $this->view->assign('form', array_merge($defaults, $_POST));
        $this->view->assign('copy_checked', Core\Functions::selectEntry('copy', 1, 0, 'checked'));

        if ($this->modules->hasPermission('frontend/captcha/index/image') === true) {
            $this->view->assign('captcha', $this->get('captcha.helpers')->captcha());
        }

        $this->secureHelper->generateFormToken($this->request->query);
    }

    public function actionImprint()
    {
        $formatter = $this->get('core.helpers.formatter.rewriteInternalUri');

        $settings = $this->contactConfig->getSettings();
        $settings['address'] = $formatter->rewriteInternalUri($settings['address']);
        $settings['disclaimer'] = $formatter->rewriteInternalUri($settings['disclaimer']);
        $this->view->assign('imprint', $settings);

        $this->view->assign('powered_by', sprintf($this->lang->t('contact', 'powered_by'), '<a href="http://www.acp3-cms.net" onclick="window.open(this.href); return false">ACP3</a>'));
    }

}