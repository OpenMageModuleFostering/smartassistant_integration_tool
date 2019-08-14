<?php

class Smartassistant_Smartassistant_Model_Rule_Condition_Product extends Mage_Rule_Model_Condition_Product_Abstract
{
//    public function loadAttributeOptions()
//    {
//        $productAttributes = Mage::getResourceSingleton('catalog/product')
//            ->loadAllAttributes()
//            ->getAttributesByCode();
//
//        $attributes = array();
//        foreach ($productAttributes as $attribute) {
//            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
//            if (!$attribute->isAllowedForRuleCondition()
//                || !$attribute->getDataUsingMethod($this->_isUsedForRuleProperty)
//            ) {
//                continue;
//            }
//            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
//        }
//
//        $this->_addSpecialAttributes($attributes);
//
//        asort($attributes);
//        $this->setAttributeOption($attributes);
//
//        return $this;
//
//
//
//        $attributes = array();
//        $this->_addSpecialAttributes($attributes);
//        asort($attributes);
//        $this->setAttributeOption($attributes);
//        return $this;
//    }

//    public function prepareConditionSql()
//    {
//        $alias     = 'cpf';
//        $attribute = $this->getAttribute();
//
//        $attrModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attribute);
//
//        $columnName = null;
//        if (
//            ($attrSourceModel = Mage::getModel($attrModel->getSourceModel()))
//            && ($flatColumns = $attrModel->getSource()->getFlatColums())
//        ) {
//            $keys = array_keys($flatColumns);
//            foreach ($keys  as $key) {
//                if (substr($key, '_value') !== false) {
//                    $attribute = $key;
//                }
//            }
//        }
//
//        $value     = $this->getValueParsed();
//        $operator  = $this->correctOperator($this->getOperator(), $this->getInputType());
//        if ($attribute == 'category_ids') {
//            $alias     = 'ccp';
//            $attribute = 'category_id';
//            $value     = $this->bindArrayOfIds($value);
//        }
//
//        /** @var $ruleResource Mage_Rule_Model_Resource_Rule_Condition_SqlBuilder */
//        $ruleResource = $this->getRuleResourceHelper();
//
//        return $ruleResource->getOperatorCondition($alias . '.' . $attribute, $operator, $value);
//    }

    /**
     * Validate product attribute value for condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $attrCode = $this->getAttribute();
        if ('category_ids' == $attrCode) {
            return $this->validateAttribute($object->getCategoryIds());
        }
        if ('attribute_set_id' == $attrCode) {
            return $this->validateAttribute($object->getData($attrCode));
        }

        $oldAttrValue = $object->hasData($attrCode) ? $object->getData($attrCode) : null;
        $object->setData($attrCode, $this->_getAttributeValue($object));
        $result = $this->_validateProduct($object);
        $this->_restoreOldAttrValue($object, $oldAttrValue);

        return (bool)$result;
    }


    /**
     * Validate product
     *
     * @param Varien_Object $object
     * @return bool
     */
    protected function _validateProduct($object)
    {
        return Mage_Rule_Model_Condition_Abstract::validate($object);
    }

    /**
     * Restore old attribute value
     *
     * @param Varien_Object $object
     * @param mixed $oldAttrValue
     */
    protected function _restoreOldAttrValue($object, $oldAttrValue)
    {
        $attrCode = $this->getAttribute();
        if (is_null($oldAttrValue)) {
            $object->unsetData($attrCode);
        } else {
            $object->setData($attrCode, $oldAttrValue);
        }
    }

    /**
     * Get attribute value
     *
     * @param Varien_Object $object
     * @return mixed
     */
    protected function _getAttributeValue($object)
    {
        $attrCode = $this->getAttribute();
        $storeId = $object->getStoreId();
        $defaultStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $productValues  = isset($this->_entityAttributeValues[$object->getId()])
            ? $this->_entityAttributeValues[$object->getId()] : array();
        $defaultValue = isset($productValues[$defaultStoreId])
            ? $productValues[$defaultStoreId] : $object->getData($attrCode);
        $value = isset($productValues[$storeId]) ? $productValues[$storeId] : $defaultValue;

        $value = $this->_prepareDatetimeValue($value, $object);
        $value = $this->_prepareMultiselectValue($value, $object);

        return $value;
    }


    /**
     * Prepare datetime attribute value
     *
     * @param mixed $value
     * @param Varien_Object $object
     * @return mixed
     */
    protected function _prepareDatetimeValue($value, $object)
    {
        $attribute = $object->getResource()->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getBackendType() == 'datetime') {
            $value = strtotime($value);
        }
        return $value;
    }

    /**
     * Prepare multiselect attribute value
     *
     * @param mixed $value
     * @param Varien_Object $object
     * @return mixed
     */
    protected function _prepareMultiselectValue($value, $object)
    {
        $attribute = $object->getResource()->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getFrontendInput() == 'multiselect') {
            $value = strlen($value) ? explode(',', $value) : array();
        }
        return $value;
    }
}
