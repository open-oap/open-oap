<?php

namespace OpenOAP\OpenOap\Domain\Validator;

use In2code\Femanager\Domain\Validator\AbstractValidator;

/**
 * Validator for strings which must include letters, numbers and special chars
 */
class StringMustIncludeValidator extends AbstractValidator
{
    /**
     * The given $value is valid if it is an alphanumeric string, which is defined as [\pL\d]*.
     *
     * @param mixed $value The value that should be validated
     */
    public function isValid($value)
    {
        if (!$this->validateMustInclude($value, 'number,letter,special')) {
            $this->addError(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('oap_validation_stringMustInclude.error', 'open_oap'), 1221551320);
        }
    }
}
