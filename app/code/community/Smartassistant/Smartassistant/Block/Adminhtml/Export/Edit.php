<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'smartassistant';
        $this->_controller = 'adminhtml_export';

        $this->_updateButton('save', 'label', Mage::helper('smartassistant')->__('Save configuration'));
        $this->_updateButton('delete', 'label', Mage::helper('smartassistant')->__('Delete configuration'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('smartassistant')->__('Save & Continue edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        if (($id=Mage::app()->getRequest()->getParam('id', null)) !== null) {
            $url = $this->getUrl('*/*/generateSend', array('id' => $id));
            $this->_addButton('generateSend', array(
                'label' => Mage::helper('smartassistant')->__('Generate & Send'),
                'onclick' => 'document.location=\''.$url.'\'',
            ), -100);
        }

        $this->_formScripts[] = "function saveAndContinueEdit(url){
                var tab = $$('.tab-item-link.active').first();
                editForm.submit($('edit_form').action+'back/edit/tab/' + tab.name);
        }";

        $this->_headerText = Mage::helper('smartassistant')->__('Edit export');
    }

    public function getLoaderHtml()
    {
        $block = $this->getLayout()->createBlock('core/template');
        $block->setTemplate('smartassistant/export/loader.phtml');
        $block->setRunLoader(Mage::app()->getRequest()->getParam('run'));
        $html = $block->toHtml();
        return $html;
    }

    protected function _toHtml() {
        return $this->getLoaderHtml() . parent::_toHtml();
    }

}
