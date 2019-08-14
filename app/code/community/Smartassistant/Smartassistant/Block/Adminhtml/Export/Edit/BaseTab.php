<?php

class Smartassistant_Smartassistant_Block_Adminhtml_Export_Edit_BaseTab
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected static $_export;

    protected static $_isInitialized;

    public function __construct() {
        parent::__construct();
        if (! self::$_isInitialized) {
            self::_init();
        }
    }

    protected static function _init()
    {
        if (($id = Mage::app()->getRequest()->getParam('id', null)) !== null) {
            self::$_export = Mage::getModel('smartassistant/export')->load($id);
        } else {
            self::$_export = Mage::getModel('smartassistant/export');
        }
        self::$_isInitialized = true;
    }

    public function export()
    {
        return self::$_export;
    }

    public function isNew()
    {
        return (! $this->export()->isEmpty());
    }

    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return '';
    }

    public function getTabTitle()
    {
        return '';
    }

    public function isHidden()
    {
        return false;
    }
}
