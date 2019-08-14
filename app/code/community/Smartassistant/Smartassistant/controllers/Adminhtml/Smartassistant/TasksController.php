<?php

class Smartassistant_Smartassistant_Adminhtml_Smartassistant_TasksController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('smartassistant/adminhtml_tasks'));
        $this->renderLayout();
    }

    public function previewAction()
    {
        $this->_title($this->__('smartassistans'))->_title($this->__('Task preview'));
        $this->loadLayout();
        $this->_setActiveMenu('catalog');
        $this->_addContent($this->getLayout()->createBlock('smartassistant/adminhtml_tasks_preview'));
        $this->renderLayout();
    }

    public function statsAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('smartassistant/adminhtml_taskStats');
        $block->setTaskId(Mage::app()->getRequest()->getParam('id', null));
        $this->getResponse()->clearHeaders();
        $this->getResponse()->setBody($block->toHtml());
    }

    private function loadCurrentModelInstance($id, $modelClass = 'smartassistant/task')
    {
        $model = Mage::getModel($modelClass)->load($id);
        if ($model->isEmpty()) {
            return Mage::getModel($modelClass);
        } else {
            return $model;
        }
    }

    protected function _isAllowed()
    {
        return true;
    }
}
