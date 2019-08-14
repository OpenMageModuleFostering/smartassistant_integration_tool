<?php

class Smartassistant_Smartassistant_Model_Mysql4_Rule extends Mage_Rule_Model_Mysql4_Rule
{
    protected function _construct()
    {
        $this->_init('smartassistant/rule', 'id');
    }
}
