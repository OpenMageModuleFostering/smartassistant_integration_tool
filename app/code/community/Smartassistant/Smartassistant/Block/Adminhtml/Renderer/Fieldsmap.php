<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Renderer_Fieldsmap
    implements Varien_Data_Form_Element_Renderer_Interface
{
    private $_map;

    public function render(\Varien_Data_Form_Element_Abstract $element)
    {
        return Mage::app()->getLayout()
           ->createBlock('adminhtml/template')
           ->setData('map', $this->getMap())
           ->setTemplate('smartassistant/export/mapping.phtml')
           ->toHtml();
    }

    public function setMap($map)
    {
        $this->_map = $map;
    }

    public function getMap()
    {
        return $this->_map;
    }
}
