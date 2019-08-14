<?php

class Smartassistant_Smartassistant_Model_Mysql4_Rule_Collection extends Mage_Rule_Model_Mysql4_Rule_Collection
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('smartassistant/rule');
    }
}
