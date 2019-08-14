<?php

class Smartassistant_Smartassistant_Model_Mysql4_Export_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('smartassistant/export');
    }
}
