<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('export_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('Export information');
    }

    protected function _beforeToHtml()
    {
        $activeTab = Mage::app()->getRequest()->getParam('tab', null);

        $this->addTab('export_general', array(
            'label' => Mage::helper('smartassistant')->__('General settings'),
            'title' => Mage::helper('smartassistant')->__('General settings'),
            'content' => $this->getLayout()->createBlock('smartassistant/adminhtml_export_edit_tab_general')->toHtml(),
            'active' => ($activeTab == 'export_general'),
        ));

        $this->addTab('rules', array(
            'label' => Mage::helper('smartassistant')->__('Conditions'),
            'title' => Mage::helper('smartassistant')->__('Conditions'),
            'content' => $this->getLayout()->createBlock('smartassistant/adminhtml_export_edit_tab_rules')->toHtml(),
            'active' => ($activeTab == 'rules'),
        ));

        $this->addTab('export_fields', array(
            'label' => Mage::helper('smartassistant')->__('Attributes settings'),
            'title' => Mage::helper('smartassistant')->__('Attributes settings'),
            'content' => $this->getLayout()->createBlock('smartassistant/adminhtml_export_edit_tab_fields')->toHtml(),
            'active' => ($activeTab == 'export_fields'),
        ));

        $this->addTab('scheduled_tasks', array(
            'label' => Mage::helper('smartassistant')->__('Scheduled tasks'),
            'title' => Mage::helper('smartassistant')->__('Scheduled tasks'),
            'content' => $this->getLayout()->createBlock('smartassistant/adminhtml_export_edit_tab_scheduledTasks')->toHtml(),
            'active' => ($activeTab == 'scheduled_tasks'),
        ));

        $this->addTab('informations', array(
            'label' => Mage::helper('smartassistant')->__('Export generation'),
            'title' => Mage::helper('smartassistant')->__('Export generation'),
            'content' => $this->getLayout()->createBlock('smartassistant/adminhtml_export_edit_tab_info')->toHtml(),
            'active' => ($activeTab == 'informations'),
        ));

        return parent::_beforeToHtml();
    }
}
