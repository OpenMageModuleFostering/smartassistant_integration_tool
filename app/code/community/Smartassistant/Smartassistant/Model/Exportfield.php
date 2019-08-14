<?php

class Smartassistant_Smartassistant_Model_Exportfield extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('smartassistant/exportfield');
    }

    /**
     * Config associated with field
     *
     * @return Smartassistant_Smartassistant_Model_Export
     */
    public function getConfig()
    {
        $config = Mage::getModel('smartassistant/export')->load($this->getExportId());
        return $config;
    }

    /**
     * Check if field data is valid
     * 
     * @return boolean
     */
    public function validate()
    {
        $exportId = $this->getExportId();
        $fieldname = $this->getFieldname();
        $attributeCode = $this->getAttributeCode();
        $position = $this->getPosition();

        return (
            ! empty($exportId)
            && ! empty($fieldname)
            && ! empty($attributeCode)
            && ! empty($position)
        );
    }
}