<?php


class Smartassistant_Smartassistant_Block_Adminhtml_TaskStats extends Mage_Core_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('smartassistant/tasks/stats.phtml');
    }

    protected function _toHtml()
    {
        $task = Mage::getModel('smartassistant/task')->load($this->getTaskId());
        $this->setStats($task->getStats());
        return parent::_toHtml();
    }
}
