<?php

class Smartassistant_Smartassistant_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve html of attribute select
     *
     * @param string $name
     * @param string $selected
     * @param array $htmlAttributes
     * @return string
     */
    public function getProductAttributesSelectHtml($name, $selected = null, $htmlAttributes = null)
    {
        /**
         * Convert array with html attributes to string
         */
        if ($htmlAttributes !== null) {
            foreach ($htmlAttributes as $attr => &$value) {
                $value = $attr . '="' . $value . '"';
            }
            $htmlAttributes = implode(' ', $htmlAttributes);
        }

        /**
         * Revceive attributes
         */
        $helper = Mage::helper('smartassistant/attribute');
        $attributes = $helper->getAttributes();
        $groups = $helper->getGroupedAttributes($attributes);

        /**
         * Create select html
         */
        $html = '<select name="'.((string)$name).'" '.((string)$htmlAttributes).'>';
        foreach ($groups as $group) {
            $html .= '<optgroup label="'.$group['label'].'">';
            foreach ($group['items'] as $attribute) {
                $html .= '<option value="'.$attribute['value'].'" '.($selected !== null && $attribute['value'] == $selected ? 'selected="selected"' : '').'>'.$attribute['label'].'</option>';
            }
            $html .= '</optgroup>';
        }
        $html .= '</select>';

        return $html;
    }
}
