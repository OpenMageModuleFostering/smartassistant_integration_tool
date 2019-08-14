<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Tasks extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_tasks';
        $this->_blockGroup = 'smartassistant';
        $this->_headerText = Mage::helper('smartassistant')->__('SMARTASSISTANT tasks');
        parent::__construct();
    }
}
