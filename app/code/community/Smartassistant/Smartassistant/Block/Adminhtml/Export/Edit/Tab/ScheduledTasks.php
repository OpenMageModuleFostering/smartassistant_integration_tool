<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_Tab_ScheduledTasks extends Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_BaseTab
{
    protected function _prepareForm()
    {
        $form  = new Varien_Data_Form();

        $scheduledTasks = $form->addFieldset('fields', array(
            'legend' => Mage::helper('smartassistant')->__('Schedule'),
        ));

        $scheduledTasks->addField('autogenerate', 'select', array(
            'name'   => 'autogenerate',
            'label'  => Mage::helper('smartassistant')->__('Autogenerate'),
            'value'  => $this->export()->getAutogenerate(),
            'values' => Mage::getModel('adminhtml/system_config_source_enabledisable')->toOptionArray(),
        ));

        $scheduledTasks->addField('autosend', 'select', array(
            'name'   => 'autosend',
            'label'  => Mage::helper('smartassistant')->__('Autosend'),
            'value'  => $this->export()->getAutosend(),
            'values' => Mage::getModel('adminhtml/system_config_source_enabledisable')->toOptionArray(),
            'disabled' => (! $this->export()->getAutogenerate()),
        ));


        $scheduledTasks->addField('days', 'multiselect', array(
            'label'    => Mage::helper('smartassistant')->__('Days'),
            'required' => false,
            'name'     => 'days',
            'values'   => Mage::getSingleton('smartassistant/system_config_source_day')->toOptionArray(),
            'value'   => $this->export()->getDays(),
            'disabled' => (! $this->export()->getAutogenerate()),
        ));

        $scheduledTasks->addField('hours', 'multiselect', array(
            'label'    => Mage::helper('smartassistant')->__('Time'),
            'required' => false,
            'name'     => 'hours',
            'values'   => Mage::getSingleton('smartassistant/system_config_source_time')->toOptionArray(),
            'value'   => $this->export()->getHours(),
            'disabled' => (! $this->export()->getAutogenerate()),
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
