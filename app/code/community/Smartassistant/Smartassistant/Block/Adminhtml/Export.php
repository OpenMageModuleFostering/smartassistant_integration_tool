<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Export extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_export';
        $this->_blockGroup = 'smartassistant';
        $this->_headerText = Mage::helper('smartassistant')->__('SMARTASSISTANT CSV configurations');
        $this->_addButtonLabel = Mage::helper('smartassistant')->__('Add configuration');
        parent::__construct();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/edit');
    }
}
