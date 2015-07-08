<?php
namespace ACP3\Modules\ACP3\Comments;

use ACP3\Core;

/**
 * Class Validator
 * @package ACP3\Modules\ACP3\Comments
 */
class Validator extends Core\Validator\AbstractValidator
{
    /**
     * @var Core\Validator\Rules\Captcha
     */
    protected $captchaValidator;
    /**
     * @var Core\ACL
     */
    protected $acl;
    /**
     * @var \ACP3\Core\Auth
     */
    protected $auth;
    /**
     * @var \ACP3\Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Core\Modules
     */
    protected $modules;
    /**
     * @var Model
     */
    protected $commentsModel;

    /**
     * @param Core\Lang                    $lang
     * @param Core\Validator\Rules\Misc    $validate
     * @param Core\Validator\Rules\Captcha $captchaValidator
     * @param Core\ACL                     $acl
     * @param Core\Auth                    $auth
     * @param Core\Date                    $date
     * @param Core\Modules                 $modules
     * @param Model                        $commentsModel
     */
    public function __construct(
        Core\Lang $lang,
        Core\Validator\Rules\Misc $validate,
        Core\Validator\Rules\Captcha $captchaValidator,
        Core\ACL $acl,
        Core\Auth $auth,
        Core\Date $date,
        Core\Modules $modules,
        Model $commentsModel
    )
    {
        parent::__construct($lang, $validate);

        $this->captchaValidator = $captchaValidator;
        $this->acl = $acl;
        $this->auth = $auth;
        $this->date = $date;
        $this->modules = $modules;
        $this->commentsModel = $commentsModel;
    }

    /**
     * @param array $formData
     * @param       $ip
     *
     * @throws Core\Exceptions\InvalidFormToken
     * @throws Core\Exceptions\ValidationFailed
     */
    public function validateCreate(array $formData, $ip)
    {
        $this->validateFormKey();

        // Flood Sperre
        $flood = $this->commentsModel->getLastDateFromIp($ip);
        $floodTime = !empty($flood) ? $this->date->timestamp($flood, true) + 30 : 0;
        $time = $this->date->timestamp('now', true);

        $this->errors = [];
        if ($floodTime > $time) {
            $this->errors[] = sprintf($this->lang->t('system', 'flood_no_entry_possible'), $floodTime - $time);
        }
        if (empty($formData['name'])) {
            $this->errors['name'] = $this->lang->t('system', 'name_to_short');
        }
        if (strlen($formData['message']) < 3) {
            $this->errors['message'] = $this->lang->t('system', 'message_to_short');
        }
        if ($this->acl->hasPermission('frontend/captcha/index/image') === true &&
            $this->auth->isUser() === false &&
            $this->captchaValidator->captcha($formData['captcha']) === false
        ) {
            $this->errors['captcha'] = $this->lang->t('captcha', 'invalid_captcha_entered');
        }

        $this->_checkForFailedValidation();
    }

    /**
     * @param array $formData
     *
     * @throws Core\Exceptions\InvalidFormToken
     * @throws Core\Exceptions\ValidationFailed
     */
    public function validateEdit(array $formData)
    {
        $this->validateFormKey();

        $this->errors = [];
        if ((empty($comment['user_id']) || $this->validate->isNumber($comment['user_id']) === false) && empty($formData['name'])) {
            $this->errors['name'] = $this->lang->t('system', 'name_to_short');
        }
        if (strlen($formData['message']) < 3) {
            $this->errors['message'] = $this->lang->t('system', 'message_to_short');
        }

        $this->_checkForFailedValidation();
    }

    /**
     * @param array $formData
     *
     * @throws Core\Exceptions\InvalidFormToken
     * @throws Core\Exceptions\ValidationFailed
     */
    public function validateSettings(array $formData)
    {
        $this->validateFormKey();

        $this->errors = [];
        if (empty($formData['dateformat']) || ($formData['dateformat'] !== 'long' && $formData['dateformat'] !== 'short')) {
            $this->errors['dateformat'] = $this->lang->t('system', 'select_date_format');
        }
        if ($this->modules->isActive('emoticons') === true && (!isset($formData['emoticons']) || ($formData['emoticons'] != 0 && $formData['emoticons'] != 1))) {
            $this->errors['emoticons'] = $this->lang->t('comments', 'select_emoticons');
        }

        $this->_checkForFailedValidation();
    }
}
