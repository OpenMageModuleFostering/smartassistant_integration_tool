<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Renderer_Widget extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
    public function render(\Varien_Data_Form_Element_Abstract $element)
    {
        return '<input type="hidden" name="parameters[unique]" value="'.uniqid().'" style="display:none;">';
    }
}
