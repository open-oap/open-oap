<?php
/**
 * User: Thorsten Born
 * Determines the language-dependent label of an option - depending on the key
 */

namespace OpenOAP\OpenOap\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetLabelOfOptionViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('value', 'string', 'Value/key of option', true);
        $this->registerArgument('options', 'array', 'All options', true);
        $this->registerArgument('multiple', 'bool', 'Does the value have to be treated as a Json?', false);
        $this->registerArgument('additionalValue', 'string', 'AdditionalValue for the last selected Item', false);
    }

    /**
     * @param $value string value of answer/may be option key
     * @param $options array all options
     * @param $multiple bool value as json?
     *
     * @return string
     */
    public function render()
    {
        $valueRaw = $this->arguments['value'];
        if ($valueRaw == '') {
            return '';
        }

        $options = $this->arguments['options'];
        $multiple = $this->arguments['multiple'];
        $additionalValue = $this->arguments['additionalValue'];

        if ($multiple) {
            $values = json_decode($valueRaw, true);
            if (!$values) {
                $values = [$valueRaw];
            }
        } else {
            $values = [$valueRaw];
        }

        if (!$options) {
            return $valueRaw;
        }
        $returnItems = [];
        $optionMap = [];

        // create fallback array
        foreach ($options as $key => $option) {
            $optionMap[$key] = $option['label'];
        }
        // add the real values
        foreach ($options as $key => $option) {
            $optionMap[$option['key']] = $option['label'];
        }

        foreach ($values as $key => $value) {
            if (!empty($optionMap[$value])) {
                $returnItems[] = $optionMap[$value];
            } elseif (!empty($optionMap[$key])) {
                // todo use translatable string or change to translatable items (not implemented yet)
                $returnItems[] = $value . ' (not translated selection)'; // $optionMap[$key] . ' ** old data - may be value is missing or changed';
            }
        }

        $output = '';
        $returnItems = str_replace('|', '<br>', $returnItems);
        if (count($returnItems) > 1) {
            $output .= '<ul class="preview__value-list">';
            $output .= '<li class="preview__value-item">';

            $output .= implode('</li><li>', $returnItems);
            if ($additionalValue !== '') {
                $output .= ': <i>' . $additionalValue . '</i>';
            }
            $output .= '</li></ul>';
        } elseif (count($returnItems) == 1) {
            $output .= $returnItems[0];
            if ($additionalValue !== '') {
                $output .= ': ' . $additionalValue;
            }
        }

        return $output;
    }
}
