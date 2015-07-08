<?php

namespace ACP3\Modules\ACP3\Newsletter;

use ACP3\Core;

/**
 * Class Helpers
 * @package ACP3\Modules\ACP3\Newsletter
 */
class Helpers
{
    /**
     * @var \ACP3\Core\Lang
     */
    protected $lang;
    /**
     * @var \ACP3\Core\Mailer
     */
    protected $mailer;
    /**
     * @var \ACP3\Core\Http\Request
     */
    protected $request;
    /**
     * @var \ACP3\Core\Router
     */
    protected $router;
    /**
     * @var \ACP3\Core\Helpers\StringFormatter
     */
    protected $stringFormatter;
    /**
     * @var \ACP3\Modules\ACP3\Newsletter\Model
     */
    protected $newsletterModel;
    /**
     * @var \ACP3\Core\Config
     */
    protected $config;

    /**
     * @param \ACP3\Core\Lang                     $lang
     * @param \ACP3\Core\Mailer                   $mailer
     * @param \ACP3\Core\Http\Request             $request
     * @param \ACP3\Core\Router                   $router
     * @param \ACP3\Core\Helpers\StringFormatter  $stringFormatter
     * @param \ACP3\Core\Config                   $config
     * @param \ACP3\Modules\ACP3\Newsletter\Model $newsletterModel
     */
    public function __construct(
        Core\Lang $lang,
        Core\Mailer $mailer,
        Core\Http\Request $request,
        Core\Router $router,
        Core\Helpers\StringFormatter $stringFormatter,
        Core\Config $config,
        Model $newsletterModel)
    {
        $this->lang = $lang;
        $this->mailer = $mailer;
        $this->request = $request;
        $this->router = $router;
        $this->stringFormatter = $stringFormatter;
        $this->config = $config;
        $this->newsletterModel = $newsletterModel;
    }

    /**
     * Versendet einen Newsletter
     *
     * @param      $newsletterId
     * @param null $recipients
     * @param bool $bcc
     *
     * @return bool
     */
    public function sendNewsletter($newsletterId, $recipients, $bcc = false)
    {
        $settings = $this->config->getSettings('newsletter');

        $newsletter = $this->newsletterModel->getOneById($newsletterId);
        $from = [
            'email' => $settings['mail'],
            'name' => $this->config->getSettings('seo')['title']
        ];

        $this->mailer
            ->reset()
            ->setBcc($bcc)
            ->setFrom($from)
            ->setSubject($newsletter['title'])
            ->setUrlWeb(HOST_NAME . $this->router->route('newsletter/archive/details/id_' . $newsletterId))
            ->setMailSignature($settings['mailsig']);

        if ($newsletter['html'] == 1) {
            $this->mailer->setTemplate('newsletter/email.tpl');
            $this->mailer->setHtmlBody($newsletter['text']);
        } else {
            $this->mailer->setBody($newsletter['text']);
        }

        $this->mailer->setRecipients($recipients);

        return $this->mailer->send();
    }

    /**
     * Meldet eine E-Mail-Adresse beim Newsletter an
     *
     * @param string $emailAddress
     *    Die anzumeldende E-Mail-Adresse
     *
     * @return boolean
     */
    public function subscribeToNewsletter($emailAddress)
    {
        $hash = md5(mt_rand(0, microtime(true)));
        $url = 'http://' . $this->request->getHostname() . $this->router->route('newsletter/index/activate/hash_' . $hash . '/mail_' . $emailAddress);

        $seoSettings = $this->config->getSettings('seo');
        $settings = $this->config->getSettings('newsletter');

        $subject = sprintf($this->lang->t('newsletter', 'subscribe_mail_subject'), $seoSettings['title']);
        $body = str_replace('{host}', $this->request->getHostname(), $this->lang->t('newsletter', 'subscribe_mail_body')) . "\n\n";

        $from = [
            'email' => $settings['mail'],
            'name' => $seoSettings['title']
        ];

        $this->mailer
            ->reset()
            ->setFrom($from)
            ->setSubject($subject)
            ->setMailSignature($settings['mailsig']);

        if ($settings['html'] == 1) {
            $this->mailer->setTemplate('newsletter/email.tpl');

            $body .= '<a href="' . $url . '">' . $url . '<a>';
            $this->mailer->setHtmlBody($this->stringFormatter->nl2p($body));
        } else {
            $body .= $url;
            $this->mailer->setBody($body);
        }

        $this->mailer->setRecipients($emailAddress);

        $mailSent = $this->mailer->send();
        $bool = false;

        // Newsletter-Konto nur erstellen, wenn die E-Mail erfolgreich versendet werden konnte
        if ($mailSent === true) {
            $insertValues = [
                'id' => '',
                'mail' => $emailAddress,
                'hash' => $hash
            ];
            $bool = $this->newsletterModel->insert($insertValues, Model::TABLE_NAME_ACCOUNTS);
        }

        return $mailSent === true && $bool !== false;
    }
}
