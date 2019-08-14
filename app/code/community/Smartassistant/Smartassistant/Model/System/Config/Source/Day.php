<?php

class Smartassistant_Smartassistant_Model_System_Config_Source_Day
{
    /**
     * Retrieve list of days
     *
     * @return string
     */
    public function toOptionArray()
    {        
       return array(
            array(
                'label' => Mage::helper('smartassistant')->__('Sunday'),
                'value' => '0'
            ),
            array(
                'label' => Mage::helper('smartassistant')->__('Monday'),
                'value' => '1'
            ),
            array(
                'label' => Mage::helper('smartassistant')->__('Tuesday'),
                'value' => '2'
            ),
            array(
                'label' => Mage::helper('smartassistant')->__('Wednesday'),
                'value' => '3'
            ),
            array(
                'label' => Mage::helper('smartassistant')->__('Thursday'),
                'value' => '4'
            ),
            array(
                'label' => Mage::helper('smartassistant')->__('Friday'),
                'value' => '5'
            ),
            array(
                'label' => Mage::helper('smartassistant')->__('Saturday'),
                'value' => '6'
            ),
        );
    }    
}
