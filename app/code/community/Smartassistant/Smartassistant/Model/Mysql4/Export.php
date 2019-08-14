<?php

class Smartassistant_Smartassistant_Model_Mysql4_Export extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('smartassistant/export', 'id');
    }
}