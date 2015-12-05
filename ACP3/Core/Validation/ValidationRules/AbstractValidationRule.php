<?php
namespace ACP3\Core\Validation\ValidationRules;

use ACP3\Core\Validation\Validator;

/**
 * Class AbstractValidationRule
 * @package ACP3\Core\Validation\ValidationRules
 */
abstract class AbstractValidationRule implements ValidationRuleInterface
{
    const NAME = '';

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @inheritdoc
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate(Validator $validator, $data, $field = '', array $extra = [])
    {
        if (!$this->isValid($data, $field, $extra)) {
            $validator->addError($this->getMessage(), $field);
        }
    }
}