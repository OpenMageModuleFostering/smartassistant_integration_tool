<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_Tab_Fields extends Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_BaseTab
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fields = $form->addFieldset('fields', array(
            'legend' => Mage::helper('smartassistant')->__('Fields configuration'),
        ));

        $map = $this->export()->getMap();

        $mapping = new Smartassistant_Smartassistant_Block_Adminhtml_Renderer_Fieldsmap();
        $mapping->setMap($map);
        $fields->setRenderer($mapping);

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
