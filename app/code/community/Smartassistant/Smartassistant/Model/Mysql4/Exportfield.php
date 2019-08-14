<?php

class Smartassistant_Smartassistant_Model_Mysql4_Exportfield extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('smartassistant/exportfield', 'id');
    }
}