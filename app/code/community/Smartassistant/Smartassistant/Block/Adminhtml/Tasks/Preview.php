<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Tasks_Preview extends Mage_Adminhtml_Block_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('smartassistant/tasks/preview.phtml');
    }

    public function getBackUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/*/');
    }

    public function getStatusUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/*/stats');
    }

    public function getTaskId()
    {
        return Mage::app()->getRequest()->getParam('id', null);
    }

    public function getTaskRefreshInterval()
    {
        return '5000';
    }

    public function getTask()
    {
        return Mage::getModel('smartassistant/task')->load($this->getTaskId());
    }

    public function getStatsBlock()
    {
        $block = $this->getLayout()->createBlock('smartassistant/adminhtml_taskStats');
        $block->setTaskId($this->getTaskId());
        return $block;
    }
}
