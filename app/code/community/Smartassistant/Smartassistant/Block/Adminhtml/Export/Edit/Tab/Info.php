<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_Tab_Info extends Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_BaseTab
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('fields', array(
            'legend' => Mage::helper('smartassistant')->__('Export generation'),
        ));

        $lastExportTime = $this->export()->lastExportTime();
        $fieldset->addField('time', 'text', array(
            'label' => Mage::helper('smartassistant')->__('Last generated'),
            'value' => $lastExportTime ? date('y-m-d H:i:s', $lastExportTime) : Mage::helper('smartassistant')->__('No export'),
            'required' => false,
            'readonly' => true,
        ));

        $url = $this->getUrl('*/*/generate', array('id' => $this->export()->getId()));
        $fieldset->addField('generate', 'button', array(
            'label' => Mage::helper('smartassistant')->__('Generate'),
            'value' => 'Generate',
            'required' => false,
            'readonly' => true,
            'onclick' => "document.location='".$url."'",
        ));

        $url = $this->getUrl('*/*/send', array('id' => $this->export()->getId()));
        $fieldset->addField('send_to_server', 'button', array(
            'label' => Mage::helper('smartassistant')->__('Send to Server'),
            'value' => 'Send',
            'required' => false,
            'readonly' => true,
            'onclick' => "document.location='".$url."'",
        ));

        $url = $this->getUrl('*/*/download', array('id' => $this->export()->getId()));
        $fieldset->addField('download', 'button', array(
            'label' => Mage::helper('smartassistant')->__('Download file'),
            'value' => 'Download',
            'required' => false,
            'readonly' => true,
            'onclick' => "document.location='".$url."'",
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
