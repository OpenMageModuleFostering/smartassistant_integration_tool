<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_Tab_Synchronization extends Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_BaseTab
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('post_form', array('legend' => Mage::helper('smartassistant')->__('Synchronization')));


        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('smartassistant')->__('Name'),
            'name' => 'name',
            'value' => $this->export()->getName(),
            'required' => true,
        ));

        $fieldset->addField('filename', 'text', array(
            'label' => Mage::helper('smartassistant')->__('Filename'),
            'name' => 'filename',
            'value' => $this->export()->getFilename(),
            'required' => true,
        ));

        if (! Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                'name' => 'stores[]',
                'label' => Mage::helper('smartassistant')->__('Store'),
                'title' => Mage::helper('smartassistant')->__('Store'),
                'required' => true,
                'value' => $this->export()->getStoreIds(),
                'values' => Mage::getSingleton('adminhtml/system_store')
                        ->getStoreValuesForForm(false, true),
            ));
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'label' => Mage::helper('smartassistant')->__('Store'),
                'title' => Mage::helper('smartassistant')->__('Store'),
                'name' => 'stores[]',
                'value' => Mage::app()->getStore(true)->getId(),
                'required' => true,
            ));
        }

        $fieldset->addField('active', 'select', array(
            'label' => Mage::helper('smartassistant')->__('Is active'),
            'name' => 'active',
            'value' => $this->export()->getIsActive(),
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('smartassistant')->__('Yes'),
                ), array(
                    'value' => 0,
                    'label' => Mage::helper('smartassistant')->__('No'),
                ),
            ),
            'required' => true,
        ));

        $this->setForm($form);
//        $form->setUseContainer(false);
        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return 'asd';
    }

    public function getTabTitle()
    {
        return 'asd 2';
    }

}
