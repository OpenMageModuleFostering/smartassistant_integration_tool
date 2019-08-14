<?php

class Smartassistant_Smartassistant_Model_Task_Config extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('smartassistant/task_config');
    }

    /**
     * Config associated with this task
     *
     * @return Smartassistant_Smartassistant_Model_Export
     */
    public function getConfig()
    {
        $config = Mage::getModel('smartassistant/export')->load($this->getExportId());
        return $config;
    }

    public function getStatus()
    {
        $config = Mage::getModel('smartassistant/task_status')->load($this->getStatusId());
        return $config;
    }
}
