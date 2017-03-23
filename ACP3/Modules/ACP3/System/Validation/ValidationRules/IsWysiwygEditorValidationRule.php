<?php
namespace ACP3\Modules\ACP3\System\Validation\ValidationRules;

use ACP3\Core\Validation\ValidationRules\AbstractValidationRule;
use ACP3\Core\WYSIWYG\WysiwygEditorRegistrar;

/**
 * Class IsWysiwygEditorValidationRule
 * @package ACP3\Modules\ACP3\System\Validation\ValidationRules
 */
class IsWysiwygEditorValidationRule extends AbstractValidationRule
{
    /**
     * @var WysiwygEditorRegistrar
     */
    private $editorRegistrar;

    /**
     * IsWysiwygEditorValidationRule constructor.
     *
     * @param WysiwygEditorRegistrar $editorRegistrar
     */
    public function __construct(WysiwygEditorRegistrar $editorRegistrar)
    {
        $this->editorRegistrar = $editorRegistrar;
    }

    /**
     * @inheritdoc
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        if (is_array($data) && array_key_exists($field, $data)) {
            return $this->isValid($data[$field], $field, $extra);
        }

        return $this->isValidWysiwygEditor($data);
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    protected function isValidWysiwygEditor($data)
    {
        return !empty($data) && $this->editorRegistrar->has($data);
    }
}
