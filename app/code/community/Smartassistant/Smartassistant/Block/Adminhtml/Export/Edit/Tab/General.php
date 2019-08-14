<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_Tab_General extends Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_BaseTab
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('post_form', array('legend' => Mage::helper('smartassistant')->__('General export information')));


        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('smartassistant')->__('Name'),
            'name' => 'name',
            'value' => $this->export()->getName(),
            'required' => true,
            'after_element_html' => '<p class="note"><span>'.Mage::helper('smartassistant')->__('Name allows you to identify the export').'</span></p>',
        ));

        $fieldset->addField('filename', 'text', array(
            'label' => Mage::helper('smartassistant')->__('Filename'),
            'name' => 'filename',
            'value' => $this->export()->getFilename(),
            'required' => true,
            'after_element_html' => '<p class="note"><span>'.Mage::helper('smartassistant')->__('Filename to be sent to FTP. CSV file extension (.csv) will be added.').'</span></p>',
        ));

        if (! Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'select', array(
                'name' => 'store_id',
                'label' => Mage::helper('smartassistant')->__('Store'),
                'title' => Mage::helper('smartassistant')->__('Store'),
                'required' => true,
                'value' => $this->export()->getStoreId(),
                'values' => Mage::getSingleton('adminhtml/system_store')
                        ->getStoreValuesForForm(false, false),
            ));
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name' => 'store_id',
                'value' => Mage::app()->getStore(true)->getId(),
                'required' => true,
            ));
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
