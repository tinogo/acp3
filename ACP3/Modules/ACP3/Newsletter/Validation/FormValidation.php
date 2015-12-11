<?php
namespace ACP3\Modules\ACP3\Newsletter\Validation;

use ACP3\Core;
use ACP3\Modules\ACP3\Captcha\Validation\ValidationRules\CaptchaValidationRule;
use ACP3\Modules\ACP3\Newsletter\Validation\ValidationRules\AccountExistsByHashValidationRule;
use ACP3\Modules\ACP3\Newsletter\Validation\ValidationRules\AccountExistsValidationRule;

/**
 * Class Validator
 * @package ACP3\Modules\ACP3\Newsletter\Validation
 */
class FormValidation extends Core\Validation\AbstractFormValidation
{
    /**
     * @param array $formData
     *
     * @throws Core\Exceptions\InvalidFormToken
     * @throws Core\Exceptions\ValidationFailed
     */
    public function validate(array $formData)
    {
        $this->validator
            ->addConstraint(Core\Validation\ValidationRules\FormTokenValidationRule::NAME)
            ->addConstraint(
                Core\Validation\ValidationRules\NotEmptyValidationRule::NAME,
                [
                    'data' => $formData,
                    'field' => 'title',
                    'message' => $this->translator->t('newsletter', 'subject_to_short')
                ])
            ->addConstraint(
                Core\Validation\ValidationRules\NotEmptyValidationRule::NAME,
                [
                    'data' => $formData,
                    'field' => 'text',
                    'message' => $this->translator->t('newsletter', 'text_to_short')
                ]);

        $this->validator->validate();
    }

    /**
     * @param array $formData
     *
     * @throws Core\Exceptions\InvalidFormToken
     * @throws Core\Exceptions\ValidationFailed
     */
    public function validateSubscribe(array $formData)
    {
        $this->validator
            ->addConstraint(Core\Validation\ValidationRules\FormTokenValidationRule::NAME)
            ->addConstraint(
                Core\Validation\ValidationRules\InArrayValidationRule::NAME,
                [
                    'data' => $formData,
                    'field' => 'salutation',
                    'message' => $this->translator->t('newsletter', 'select_salutation'),
                    'extra' => [
                        'haystack' => [1, 2]
                    ]
                ])
            ->addConstraint(
                Core\Validation\ValidationRules\EmailValidationRule::NAME,
                [
                    'data' => $formData,
                    'field' => 'mail',
                    'message' => $this->translator->t('system', 'wrong_email_format')
                ])
            ->addConstraint(
                AccountExistsValidationRule::NAME,
                [
                    'data' => $formData,
                    'field' => 'mail',
                    'message' => $this->translator->t('newsletter', 'account_not_exists')
                ])
            ->addConstraint(
                CaptchaValidationRule::NAME,
                [
                    'data' => $formData,
                    'field' => 'captcha',
                    'message' => $this->translator->t('captcha', 'invalid_captcha_entered')
                ]);

        $this->validator->validate();
    }

    /**
     * @param array $formData
     *
     * @throws Core\Exceptions\InvalidFormToken
     * @throws Core\Exceptions\ValidationFailed
     */
    public function validateUnsubscribe(array $formData)
    {
        $this->validator
            ->addConstraint(Core\Validation\ValidationRules\FormTokenValidationRule::NAME)
            ->addConstraint(
                Core\Validation\ValidationRules\EmailValidationRule::NAME,
                [
                    'data' => $formData,
                    'field' => 'mail',
                    'message' => $this->translator->t('system', 'wrong_email_format')
                ])
            ->addConstraint(
                AccountExistsValidationRule::NAME,
                [
                    'data' => $formData,
                    'field' => 'mail',
                    'message' => $this->translator->t('newsletter', 'account_not_exists')
                ])
            ->addConstraint(
                CaptchaValidationRule::NAME,
                [
                    'data' => $formData,
                    'field' => 'captcha',
                    'message' => $this->translator->t('captcha', 'invalid_captcha_entered')
                ]);


        $this->validator->validate();
    }

    /**
     * @param array $formData
     *
     * @throws Core\Exceptions\InvalidFormToken
     * @throws Core\Exceptions\ValidationFailed
     */
    public function validateSettings(array $formData)
    {
        $this->validator
            ->addConstraint(Core\Validation\ValidationRules\FormTokenValidationRule::NAME)
            ->addConstraint(
                Core\Validation\ValidationRules\EmailValidationRule::NAME,
                [
                    'data' => $formData,
                    'field' => 'mail',
                    'message' => $this->translator->t('system', 'wrong_email_format')
                ]);

        $this->validator->validate();
    }

    /**
     * @param string $hash
     *
     * @throws Core\Exceptions\ValidationFailed
     */
    public function validateActivate($hash)
    {
        $this->validator
            ->addConstraint(
                AccountExistsByHashValidationRule::NAME,
                [
                    'data' => $hash,
                    'message' => $this->translator->t('newsletter', 'account_not_exists')
                ]);

        $this->validator->validate();
    }
}
